<?php

/**
 * Description of HTTP
 * @author coder
 *
 * Created on: Mar 22, 2024 at 4:44:50 PM
 */

namespace library\client {

    use library\Concurrency;
    use shani\engine\core\Definitions;

    final class HTTP
    {

        private string $host;
        private int $retries;
        private array $headers = [], $files = [], $cookies = [], $curlOptions;
        private ?string $signatureKey = null, $signatureAlgorithm = null;
        private ?string $signatureHeader = null, $cipherKey = null;
        private ?string $cipherAlgorithm = null, $initVector = null;
        private array $specialOptions = [];

        public function __construct(string $host, int $retries = 3, int $timeout = 20)
        {
            $this->host = $host;
            $this->retries = $retries;
            $this->curlOptions = [
                CURLOPT_SSL_VERIFYPEER => true, CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true, CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_HEADER => true, CURLOPT_CONNECTTIMEOUT => $timeout,
                CURLOPT_UPLOAD_BUFFERSIZE => Definitions::BUFFER_SIZE,
                CURLOPT_BUFFERSIZE => Definitions::BUFFER_SIZE
            ];
        }

        /**
         * Set HTTP request headers
         * @param array $headers Associative array of header and header-value
         * @return self
         */
        public function headers(array $headers): self
        {
            foreach ($headers as $key => $value) {
                $this->headers[strtolower(trim($key))] = $value;
            }
            return $this;
        }

        public function uri(): \library\URI
        {
            return new \library\URI($this->host);
        }

        public function withCookie(Cookie $cookie, string $cookieFile = null): self
        {
            $copy = clone $this;
            $copy->cookies = null;
            return $copy->cookies($cookie, $cookieFile);
        }

        public function withHeaders(array $headers): self
        {
            $copy = clone $this;
            return $copy->headers($headers);
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

        public function withoutCookies(?array $names = null): self
        {
            $copy = clone $this;
            if ($names === null) {
                $copy->cookies = null;
                return $copy;
            }
            $cookies = \library\Map::get($this->cookies, $names, false);
            return $copy->withCookie($cookies);
        }

        public function withoutHeaders(array $names): self
        {
            $headers = \library\Map::get($this->headers, $names, false);
            return $this->withHeaders($headers);
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
         * Copy HTTP body then change the host, leaving other options unchanged
         * @param string $host New hostname
         * @return self
         */
        public function withHost(string $host): self
        {
            if ($host === $this->host) {
                return $this;
            }
            $copy = clone $this;
            $copy->host = $host;
            return $copy;
        }

        /**
         * Send HTTP request using any request method
         * @param string|null $reqMethod Request method e.g: GET, PUT, POST etc
         * @param string $endpoint Request destination endpoint
         * @param type $body Request body
         * @param callable $callback A callback that must accept Response object
         * @return self
         */
        public function send(?string $reqMethod, string $endpoint, $body = null, callable $callback = null): self
        {
            Concurrency::async(function ()use (&$reqMethod, &$endpoint, &$body, &$callback) {
                $stream = null;
                $this->finalize($reqMethod, $body);
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

        private function done(&$stream, string $endpoint, ?callable &$callback): void
        {
            $retry = 0;
            $curl = curl_init($this->host . $endpoint);
            curl_setopt_array($curl, $this->curlOptions);
            $this->specialOptions[CURLOPT_FILE] = $stream;
            curl_setopt_array($curl, $this->specialOptions);
            while (curl_exec($curl) === false && $retry < $this->retries) {
                Concurrency::sleep(++$retry);
            }
            if ($callback !== null) {
                $callback(new Response($curl, $stream));
            }
            curl_close($curl);
        }

        private function finalize(?string $method, &$body): void
        {
            if (!empty($this->files)) {
                $body = !empty($body) ? array_merge($body, $this->files) : $this->files;
            }
            if (!empty($body)) {
                if ($this->signatureKey !== null) {
                    $this->headers([$this->signatureHeader => $this->sign($body)]);
                }
                if ($this->cipherKey !== null) {
                    $this->setOptions([
                        CURLOPT_POSTFIELDS => $this->encrypt($this->convertBody($body))
                    ]);
                } else {
                    $this->setOptions([CURLOPT_POSTFIELDS => $this->convertBody($body)]);
                }
            }
            if ($method !== null) {
                $this->setOptions([CURLOPT_CUSTOMREQUEST => $method]);
            }
            $this->setHeaders();
        }

        private function convertBody(&$body)
        {
            if (!empty($this->headers['content-type']) && is_array($body)) {
                $type = \library\Mime::explode($this->headers['content-type'])[1];
                return \library\DataConvertor::convertTo($body, $type);
            }
            return $body;
        }

        /**
         * Set HTTP cookies
         * @param \library\HttpCookie $cookie Cookie object to send
         * @return self
         */
        public function cookies(\library\HttpCookie $cookie): self
        {
            $this->cookies[$cookie->name()] = $cookie;
            $this->setOptions([CURLOPT_COOKIE => $cookie]);
            return $this;
        }

        /**
         * Copy HTTP object without attachments
         * @return self
         */
        public function withoutAttachments(): self
        {
            $copy = clone $this;
            $copy->files = [];
            return $copy;
        }

        /**
         * Send files as attachments
         * @param array $filenames Associative array where key is the name of the file
         * and value is the actual path to a file.
         * @return self
         */
        public function attachments(array $filenames): self
        {
            foreach ($filenames as $name => $path) {
                $this->files[$name] = curl_file_create($path, \library\Mime::fromFilename($path), basename($path));
            }
            return $this;
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

        private function setHeaders(): self
        {
            if (!empty($this->headers)) {
                $values = [];
                foreach ($this->headers as $key => $value) {
                    $values[] = ucwords($key, '-') . ': ' . $value;
                }
                $this->setOptions([CURLOPT_HTTPHEADER => $values]);
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
         * @param type $body Request body
         * @param callable $callback A callback that must accept Response object
         * @return self
         */
        public function get(string $endpoint, $body = null, callable $callback = null): self
        {
            return $this->send('GET', self::merge($endpoint, $body), null, $callback);
        }

        /**
         * Send HTTP POST request to destination
         * @param string $endpoint Request destination endpoint
         * @param type $body Request body
         * @param callable $callback A callback that must accept Response object
         * @return self
         */
        public function post(string $endpoint, $body = null, callable $callback = null): self
        {
            return $this->send('POST', $endpoint, $body, $callback);
        }

        /**
         * Send HTTP PUT request to destination
         * @param string $endpoint Request destination endpoint
         * @param type $body Request body
         * @param callable $callback A callback that must accept Response object
         * @return self
         */
        public function put(string $endpoint, $body = null, callable $callback = null): self
        {
            return $this->send('PUT', $endpoint, $body, $callback);
        }

        /**
         * Send HTTP PATCH request to destination
         * @param string $endpoint Request destination endpoint
         * @param type $body Request body
         * @param callable $callback A callback that must accept Response object
         * @return self
         */
        public function patch(string $endpoint, $body = null, callable $callback = null): self
        {
            return $this->send('PATCH', $endpoint, $body, $callback);
        }

        /**
         * Send HTTP DELETE request to destination
         * @param string $endpoint Request destination endpoint
         * @param type $body Request body
         * @param callable $callback A callback that must accept Response object
         * @return self
         */
        public function delete(string $endpoint, $body = null, callable $callback = null): self
        {
            return $this->send('DELETE', $endpoint, $body, $callback);
        }

        /**
         * Send HTTP HEAD request to destination
         * @param string $endpoint Request destination endpoint
         * @param type $body Request body
         * @param callable $callback A callback that must accept Response object
         * @return self
         */
        public function head(string $endpoint, $body = null, callable $callback = null): self
        {
            return $this->send('HEAD', $endpoint, $body, $callback);
        }

        /**
         * Download HTTP response as a file
         * @param string $endpoint Request destination endpoint
         * @param string $destination Location on disk to save a file
         * @param callable $callback A callback for showing progress during download
         * where the first argument is total bytes to load and the second is
         * total bytes loaded
         * @return self
         */
        public function download(string $endpoint, string $destination, callable $callback = null): self
        {
            $this->specialOptions = [
                CURLOPT_FILE => $destination, CURLOPT_HEADER => false
            ];
            if ($callback !== null) {
                $this->specialOptions[CURLOPT_NOPROGRESS] = true;
                $this->specialOptions[CURLOPT_PROGRESSFUNCTION] = function (&$fp, $total, $loaded) use (&$callback) {
                    $callback($total, $loaded);
                };
            }
            return $this->get($endpoint);
        }

        /**
         * Upload a file to remote server
         * @param string $endpoint Request destination endpoint
         * @param string $source A path to a source file to upload
         * @param callable $callback A callback for showing progress during upload
         * where the first argument is total bytes to load and the second is
         * total bytes loaded
         * @return self
         */
        public function upload(string $endpoint, string $source, callable $callback = null): self
        {
            $fp = fopen($source, 'rb');
            $this->specialOptions = [
                CURLOPT_INFILE => $fp, CURLOPT_UPLOAD => true,
                CURLOPT_INFILESIZE => fstat($fp)['size']
            ];
            if ($callback !== null) {
                $this->specialOptions[CURLOPT_NOPROGRESS] = true;
                $this->specialOptions[CURLOPT_PROGRESSFUNCTION] = function (&$fp, $dt, $dl, $total, $loaded) use (&$callback) {
                    $callback($total, $loaded);
                };
            }
            return $this->send(null, $endpoint);
        }

        private static function merge(string $endpoint, $body): string
        {
            if ($body !== null && !is_callable($body)) {
                $connector = str_contains($endpoint, '?') ? '&' : '?';
                $endpoint .= $connector . (is_array($body) ? http_build_query($body) : $body);
            }
            return $endpoint;
        }
    }

}
