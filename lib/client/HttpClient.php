<?php

/**
 * Description of HTTP
 * @author coder
 *
 * Created on: Mar 22, 2024 at 4:44:50 PM
 */

namespace lib\client {

    use lib\Concurrency;
    use lib\DataConvertor;
    use lib\http\RequestEntity;
    use shani\core\Definitions;

    final class HttpClient
    {

        private int $retries;
        private array $curlOptions;
        private ?string $signatureKey = null, $signatureAlgorithm = null;
        private ?string $signatureHeader = null, $cipherKey = null;
        private ?string $cipherAlgorithm = null, $initVector = null;
        private array $specialOptions = [];
        private readonly RequestEntity $request;

        public function __construct(RequestEntity $request, int $retries = 3, int $timeout = 20)
        {
            $this->retries = $retries;
            $this->curlOptions = [
                CURLOPT_SSL_VERIFYPEER => true, CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true, CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_HEADER => true, CURLOPT_CONNECTTIMEOUT => $timeout,
                CURLOPT_UPLOAD_BUFFERSIZE => Definitions::BUFFER_SIZE,
                CURLOPT_BUFFERSIZE => Definitions::BUFFER_SIZE
            ];
            $this->request = $request;
        }

        public function withRequest(RequestEntity $request): self
        {
            $copy = clone $this;
            $copy->request = $request;
            return $copy;
        }

        public function withoutProxy(): self
        {
            $copy = clone $this;
            $copy->setOptions([
                CURLOPT_PROXY => null, CURLOPT_HTTPPROXYTUNNEL => false,
                CURLOPT_PROXYUSERPWD => null
            ]);
            return $copy;
        }

        /**
         * Set proxy server for this request
         * @param string $host Server name or ip address
         * @param string $username server username
         * @param string $password server password
         * @return self
         */
        public function proxy(string $host, string $username = null, string $password = null): self
        {
            $this->setOptions([CURLOPT_PROXY => $host, CURLOPT_HTTPPROXYTUNNEL => true]);
            if ($username !== null) {
                $this->setOptions([CURLOPT_PROXYUSERPWD => $username . ':' . $password]);
            }
            return $this;
        }

        /**
         * Set encryption credentials to be used on encryption/decryption of HTTP message
         * @param string $key Encryption key
         * @param string $initVector Initialization vector
         * @param string $algorithm Encryption algorithm
         * @return self
         */
        public function encryption(string $key, string $initVector, string $algorithm = 'aes-256-cbc'): self
        {
            $this->cipherKey = $key;
            $this->cipherAlgorithm = $algorithm;
            $this->initVector = $initVector;
            return $this;
        }

        /**
         * Sign data using provided signature credentials
         * @param string $data Data to sign
         * @return string
         * @throws \RuntimeException
         */
        private function sign(string $data): string
        {
            try {
                return hash_hmac($this->signatureAlgorithm, $data, $this->signatureKey);
            } catch (\ErrorException $e) {
                throw new \RuntimeException('Missing signature key.');
            }
        }

        /**
         * Encrypt data using provided encryption credentials
         * @param string $data Data to encrypt
         * @return string Encrypted data
         * @throws \RuntimeException
         */
        private function encrypt(string $data): string
        {
            try {
                return openssl_encrypt($data, $this->cipherAlgorithm, $this->cipherKey, 0, $this->initVector);
            } catch (\ErrorException $e) {
                throw new \RuntimeException('Missing encryption key.');
            }
        }

        /**
         * Decrypt once encrypted data using provided decryption keys
         * @param string $data Encrypted data
         * @return string Decrypted data
         * @throws \RuntimeException
         */
        public function decrypt(string $data): string
        {
            try {
                return openssl_decrypt($data, $this->cipherAlgorithm, $this->cipherKey, 0, $this->initVector);
            } catch (\ErrorException $e) {
                throw new \RuntimeException('Missing decryption key.');
            }
        }

        public static function cipherKeys(string $algorithm): array
        {
            $keyLen = openssl_cipher_key_length($algorithm);
            $ivLen = openssl_cipher_iv_length($algorithm);
            return [
                'key' => base64_encode(openssl_random_pseudo_bytes($keyLen)),
                'iv' => base64_encode(openssl_random_pseudo_bytes($ivLen))
            ];
        }

        /**
         * Send HTTP request using provided request method
         * @param string $method Request method e.g: GET, PUT, POST etc
         * @param string $endpoint Request destination endpoint
         * @param callable $callback A callback that must accept ResponseEntity object
         * @return self
         */
        public function send(string $method, string $endpoint, callable $callback): self
        {
            Concurrency::async(function ()use (&$method, &$endpoint, &$callback) {
                $stream = null;
                $this->finalize($method);
                if (!isset($this->specialOptions[CURLOPT_FILE])) {
                    $stream = fopen('php://temp', 'r+b');
                } else {
                    $stream = fopen($this->specialOptions[CURLOPT_FILE], 'a+b');
                    $this->specialOptions[CURLOPT_RESUME_FROM] = fstat($stream)['size'];
                }
                $this->done($stream, $endpoint, $callback);
                fclose($stream);
            });
            return $this;
        }

        private function setCookies(): void
        {
            $cookies = $this->request->cookie->toArray();
            if (!empty($cookies)) {
                foreach ($cookies as $cookie) {
                    $this->setOptions([CURLOPT_COOKIE => $cookie]);
                }
            }
        }

        private function done(&$stream, string $endpoint, ?callable &$callback): void
        {
            $retry = 0;
            $curl = curl_init($this->request->uri->host() . $endpoint);
            if ($curl === false) {
                throw new \ErrorException('Connection to a host failed.');
            }
            curl_setopt_array($curl, $this->curlOptions);
            $this->specialOptions[CURLOPT_FILE] = $stream;
            curl_setopt_array($curl, $this->specialOptions);
            while (curl_exec($curl) === false && $retry < $this->retries) {
                Concurrency::sleep(++$retry);
            }
            $callback(ResponseBuilder::build($this->request, $curl, $stream));
            curl_close($curl);
        }

        private function finalize(string $method): void
        {
            $body = $this->mergeBodyWithFiles();
            if (!empty($body)) {
                if ($this->signatureKey !== null) {
                    $this->request->header()->addOne($this->signatureHeader, $this->sign($body));
                }
                $content = DataConvertor::convertTo($body, $this->request->type);
                if ($this->cipherKey !== null) {
                    $this->setOptions([
                        CURLOPT_POSTFIELDS => $this->encrypt($content)
                    ]);
                } else {
                    $this->setOptions([CURLOPT_POSTFIELDS => $content]);
                }
            }
            $this->setOptions([
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => $this->request->header()->toArray()
            ]);
        }

        private function mergeBodyWithFiles(): array
        {
            $files = [];
            foreach ($this->request->files as $name => $file) {
                $files[$name] = curl_file_create($file->stream->getPathname(), $file->type, $file->name);
            }
            $body = $this->request->body->toArray();
            if (!empty($files)) {
                return !empty($body) ? array_merge($body, $files) : $files;
            }
            return $body;
        }

        /**
         * Set additional CURL options to use during this HTTP session
         * @param array $options CURL options
         * @return self
         */
        public function setOptions(array $options): self
        {
            foreach ($options as $key => $value) {
                $this->curlOptions[$key] = $value;
            }
            return $this;
        }

        /**
         * Sign HTTP request with signature
         * @param string|null $secretKey Secret key used for signing
         * @param string $algorithm Signature algorithm supported by hash_hmac_algos()
         * @param string $headerName HTTP Header that will hold signature
         * @return self
         * @see hash_hmac_algos()
         */
        public function signature(?string $secretKey, string $algorithm = 'sha256', string $headerName = 'X-Signature'): self
        {
            $this->signatureKey = $secretKey;
            $this->signatureAlgorithm = $algorithm;
            $this->signatureHeader = $headerName;
            return $this;
        }

        /**
         * Copy HTTP object without signature key
         * @return self
         */
        public function withoutSignature(): self
        {
            $copy = clone $this;
            $copy->signatureKey = $copy->signatureAlgorithm = $copy->signatureHeader = null;
            return $copy;
        }

        /**
         * Copy HTTP object without encryption credentials
         * @return self
         */
        public function withoutEncryption(): self
        {
            $copy = clone $this;
            $this->cipherKey = $this->cipherAlgorithm = $this->initVector = null;
            return $copy;
        }

        /**
         * Append authenticate headers to HTTP request using supported authorization method
         * @param string $username Username
         * @param string $password Password
         * @param int $authMethod Authorization method can be one of the CURLAUTH_*
         * @return self
         */
        public function auth(string $username, string $password, int $authMethod = CURLAUTH_BEARER): self
        {
            $this->setOptions([
                CURLOPT_HTTPAUTH => $authMethod,
                CURLOPT_USERPWD => $username . ':' . $password
            ]);
            return $this;
        }

        /**
         * Encrypt HTTP payload using provided SSL credentials
         * @param string $caPath Path to certificate authority file
         * @param string $keyPath Path to SSL public key file
         * @param int $sslVersion One of the CURL_SSLVERSION_*
         * @return self
         */
        public function ssl(string $caPath, string $keyPath, int $sslVersion = CURL_SSLVERSION_MAX_DEFAULT): self
        {
            $this->setOptions([
                CURLOPT_CAINFO => $caPath,
                CURLOPT_SSLKEY => $keyPath,
                CURLOPT_SSLVERSION => $sslVersion
            ]);
            return $this;
        }

        /**
         * Send HTTP GET request to destination
         * @param string $endpoint Request destination endpoint
         * @param callable $callback A callback that must accept ResponseEntity object
         * @return self
         */
        public function get(string $endpoint, callable $callback): self
        {
            return $this->send('GET', self::merge($endpoint, $this->request->body->toArray()), $callback);
        }

        /**
         * Send HTTP POST request to destination
         * @param string $endpoint Request destination endpoint
         * @param callable $callback A callback that must accept ResponseEntity object
         * @return self
         */
        public function post(string $endpoint, callable $callback): self
        {
            return $this->send('POST', $endpoint, $callback);
        }

        /**
         * Send HTTP PUT request to destination
         * @param string $endpoint Request destination endpoint
         * @param type $body Request body
         * @param callable $callback A callback that must accept ResponseEntity object
         * @return self
         */
        public function put(string $endpoint, callable $callback): self
        {
            return $this->send('PUT', $endpoint, $callback);
        }

        /**
         * Send HTTP PATCH request to destination
         * @param string $endpoint Request destination endpoint
         * @param callable $callback A callback that must accept ResponseEntity object
         * @return self
         */
        public function patch(string $endpoint, callable $callback): self
        {
            return $this->send('PATCH', $endpoint, $callback);
        }

        /**
         * Send HTTP DELETE request to destination
         * @param string $endpoint Request destination endpoint
         * @param callable $callback A callback that must accept ResponseEntity object
         * @return self
         */
        public function delete(string $endpoint, callable $callback): self
        {
            return $this->send('DELETE', $endpoint, $callback);
        }

        /**
         * Send HTTP HEAD request to destination
         * @param string $endpoint Request destination endpoint
         * @param callable $callback A callback that must accept ResponseEntity object
         * @return self
         */
        public function head(string $endpoint, callable $callback): self
        {
            return $this->send('HEAD', $endpoint, $callback);
        }

        /**
         * Download HTTP response as a file
         * @param string $endpoint Request destination endpoint
         * @param string $destination Location on disk to save a file
         * @param callable $callback A callback that must accept ResponseEntity object
         * @param callable $progress A callback for showing progress during download
         * where the first argument is total bytes to load and the second is
         * total bytes loaded
         * @return self
         */
        public function download(string $endpoint, string $destination, callable $callback, callable $progress = null): self
        {
            $this->specialOptions = [
                CURLOPT_FILE => $destination, CURLOPT_HEADER => false
            ];
            if ($progress !== null) {
                $this->specialOptions[CURLOPT_NOPROGRESS] = true;
                $this->specialOptions[CURLOPT_PROGRESSFUNCTION] = function (&$fp, $total, $loaded) use (&$progress) {
                    $progress($total, $loaded);
                };
            }
            return $this->get($endpoint, $callback);
        }

        /**
         * Upload a file to remote server
         * @param string $endpoint Request destination endpoint
         * @param string $source A path to a source file to upload
         * @param callable $callback A callback that must accept ResponseEntity object
         * @param callable $progress A callback for showing progress during upload
         * where the first argument is total bytes to load and the second is
         * total bytes loaded
         * @return self
         */
        public function upload(string $endpoint, string $source, callable $callback, callable $progress = null): self
        {
            $fp = fopen($source, 'rb');
            $this->specialOptions = [
                CURLOPT_INFILE => $fp, CURLOPT_UPLOAD => true,
                CURLOPT_INFILESIZE => fstat($fp)['size']
            ];
            if ($progress !== null) {
                $this->specialOptions[CURLOPT_NOPROGRESS] = true;
                $this->specialOptions[CURLOPT_PROGRESSFUNCTION] = function (&$fp, $dt, $dl, $total, $loaded) use (&$progress) {
                    $progress($total, $loaded);
                };
            }
            return $this->send($this->request->method, $endpoint, $callback);
        }

        private static function merge(string $endpoint, ?array $body): string
        {
            if (!empty($body)) {
                $connector = str_contains($endpoint, '?') ? '&' : '?';
                $endpoint .= $connector . http_build_query($body);
            }
            return $endpoint;
        }
    }

}
