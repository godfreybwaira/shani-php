<?php

/**
 * Description of HTTP
 * @author coder
 *
 * Created on: Mar 22, 2024 at 4:44:50 PM
 */

namespace lib\client {

    use lib\Concurrency;
    use lib\crypto\DigitalSignature;
    use lib\crypto\Encryption;
    use lib\File;
    use lib\http\HttpHeader;
    use lib\http\HttpStatus;
    use lib\http\ResponseEntity;
    use lib\RequestEntityBuilder;
    use lib\URI;
    use shani\core\Definitions;

    final class HttpClient
    {

        private int $retries;
        private bool $asyncMode = true;
        private array $curlOptions, $specialOptions = [], $body = [];
        private HttpHeader $header;
        private ?DigitalSignature $signature = null;
        private ?Encryption $encryption = null;
        private ?string $headerName = null;
        private readonly string $host;
        private array $files = [];

        /**
         * Create HTTP connection to a remote server
         * @param URI $uri URI object
         * @param int $retries number of time a client has to retry connecting to a remote server
         * @param int $timeout Timeout in seconds before this client terminating the connection if the server is not responding
         */
        public function __construct(URI $uri, int $retries = 3, int $timeout = 20)
        {
            $this->retries = $retries;
            $this->curlOptions = [
                CURLOPT_SSL_VERIFYPEER => true, CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true, CURLOPT_SSL_VERIFYHOST => 2,
                CURLOPT_HEADER => true, CURLOPT_CONNECTTIMEOUT => $timeout,
                CURLOPT_UPLOAD_BUFFERSIZE => Definitions::BUFFER_SIZE,
                CURLOPT_BUFFERSIZE => Definitions::BUFFER_SIZE
            ];
            $this->header = new HttpHeader();
            $this->host = $uri->host();
        }

        /**
         * Whether to enable asynchronous communication between client and server
         * machine. Default is true
         * @param bool $enable
         * @return self
         */
        public function enableAsync(bool $enable = true): self
        {
            $this->asyncMode = $enable;
            return $this;
        }

        /**
         * Clone existing request and return a new request but with new header object.
         * @param HttpHeader $header
         * @return self
         */
        public function withHeader(HttpHeader $header): self
        {
            $copy = clone $this;
            $copy->header = $header;
            return $copy;
        }

        /**
         * Set HTTP headers, overriding existing headers
         * @param HttpHeader $header
         * @return self
         */
        public function setHeader(HttpHeader $header): self
        {
            $this->header = $header;
            return $this;
        }

        public function withoutBody(): self
        {
            $copy = clone $this;
            $copy->body = [];
            return $copy;
        }

        /**
         * Clone existing request and return a new request but with new body.
         * @param array $body
         * @return self
         */
        public function withBody(array $body): self
        {
            $copy = clone $this;
            $copy->body = $body;
            return $copy;
        }

        public function setBody(array $body): self
        {
            $this->body = $body;
            return $this;
        }

        /**
         * Add a file to send together with request.
         * @param File $file File object
         * @return self
         */
        public function addFile(File $file): self
        {
            $this->files[$file->name] = $file;
            return $this;
        }

        /**
         * Clone existing request and return a new request but with additional file(s).
         * @param File $file File object
         * @return self
         */
        public function withFile(File $file): self
        {
            $copy = clone $this;
            $copy->files[$file->name] = $file;
            return $copy;
        }

        /**
         * Clone existing request and return a new request without file(s).
         * @return self
         */
        public function withoutFiles(): self
        {
            $copy = clone $this;
            $copy->files = [];
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
         * @param URI $uri URI pointing to a proxy server machine
         * @param string $username server username
         * @param string $password server password
         * @return self
         */
        public function proxy(URI $uri, string $username = null, string $password = null): self
        {
            $this->setOptions([CURLOPT_PROXY => $uri->host(), CURLOPT_HTTPPROXYTUNNEL => true]);
            if ($username !== null) {
                $this->setOptions([CURLOPT_PROXYUSERPWD => $username . ':' . $password]);
            }
            return $this;
        }

        /**
         * Set encryption credentials to be used on encryption/decryption of HTTP message
         * @param Encryption $encryption Encryption object
         * @return self
         */
        public function encryption(Encryption $encryption): self
        {
            $this->encryption = $encryption;
            return $this;
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
            if ($this->asyncMode) {
                Concurrency::async(fn() => $this->sendSync($method, $endpoint, $callback));
            } else {
                $this->sendSync($method, $endpoint, $callback);
            }
            return $this;
        }

        private function sendSync(string $method, string $endpoint, callable &$callback): void
        {
            $stream = null;
            $this->createBody();
            if (!isset($this->specialOptions[CURLOPT_FILE])) {
                $stream = fopen('php://temp', 'r+b');
            } else {
                $stream = fopen($this->specialOptions[CURLOPT_FILE], 'a+b');
                $this->specialOptions[CURLOPT_RESUME_FROM] = fstat($stream)['size'];
            }
            $this->done($stream, $method, new URI($this->host . $endpoint), $callback);
            fclose($stream);
        }

        private function done(&$stream, string $method, URI $endpoint, callable &$callback): void
        {
            $retry = 0;
            while (($curl = curl_init($endpoint)) === false && $retry < $this->retries) {
                Concurrency::sleep(++$retry);
            }
            if ($curl === false) {
                throw new \Exception('Connection to a host machine failed.');
            }
            $this->setOptions([
                CURLOPT_CUSTOMREQUEST => $method,
                CURLOPT_HTTPHEADER => $this->header->map(fn($name, $value) => $name . ':' . $value)->toArray()
            ]);
            curl_setopt_array($curl, $this->curlOptions);
            $this->specialOptions[CURLOPT_FILE] = $stream;
            curl_setopt_array($curl, $this->specialOptions);
            if (curl_exec($curl) === false) {
                throw new \Exception('Failed to execute command.');
            }
            $response = $this->prepareResponse($curl, $stream, $method, $endpoint);
            if ($this->signature !== null) {
                $response = $this->verifySignature($response);
            }
            if ($this->encryption !== null) {
                $response->setBody($this->encryption->decrypt($response->body()));
            }
            $callback($response);
            curl_close($curl);
        }

        public function prepareResponse(\CurlHandle &$curl, &$stream, string $method, URI &$endpoint): ResponseEntity
        {
            $request = (new RequestEntityBuilder())
                    ->headers($this->header)
                    ->files($this->files)
                    ->body($this->body)
                    ->method($method)
                    ->uri($endpoint)
                    ->ip(curl_getinfo($curl, CURLINFO_LOCAL_IP))
                    ->protocol(curl_getinfo($curl, CURLINFO_PROTOCOL))
                    ->cookies($this->header->getCookies())
                    ->build();
            return ResponseBuilder::build($request, $curl, $stream);
        }

        private function createBody(): void
        {
            $body = $this->mergeBodyWithFiles();
            if (empty($body)) {
                return;
            }
            $content = http_build_query($body);
            if ($this->signature !== null) {
                $this->header->addOne($this->headerName, $this->signature->sign($content));
            }
            if ($this->encryption !== null) {
                $content = $this->encryption->encrypt($content);
            }
            $this->setOptions([CURLOPT_POSTFIELDS => $content]);
        }

        private function mergeBodyWithFiles(): array
        {
            $files = [];
            foreach ($this->files as $name => $file) {
                $files[$name] = new \CURLFile($file->path, $file->type, basename($file->path));
            }
            if (!empty($files)) {
                return !empty($this->body) ? array_merge($this->body, $files) : $files;
            }
            return $this->body;
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
         * @param DigitalSignature $signature
         * @param string $headerName HTTP Header that will hold signature. Default is <code>X-Signature</code>
         * @return self
         */
        public function signature(DigitalSignature $signature, string $headerName = 'X-Signature'): self
        {
            $this->signature = $signature;
            $this->headerName = $headerName;
            return $this;
        }

        /**
         * Copy HTTP object without signature key
         * @return self
         */
        public function withoutSignature(): self
        {
            $copy = clone $this;
            $copy->signature = null;
            return $copy;
        }

        /**
         * Copy HTTP object without encryption credentials
         * @return self
         */
        public function withoutEncryption(): self
        {
            $copy = clone $this;
            $copy->encryption = null;
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
            return $this->send('GET', self::merge($endpoint, $this->body), $callback);
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
         * @param callable $callback A callback that accept ResponseEntity object
         * as a parameter
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
            return $this->put($endpoint, $callback);
        }

        private static function merge(string $endpoint, ?array $body): string
        {
            if (!empty($body)) {
                $connector = str_contains($endpoint, '?') ? '&' : '?';
                $endpoint .= $connector . http_build_query($body);
            }
            return $endpoint;
        }

        private function verifySignature(ResponseEntity $response): ResponseEntity
        {
            try {
                $signature = $response->header()->getOne($this->headerName);
                $this->signature->verify($response->body(), $signature);
            } catch (\Exception $exc) {
                $response->setStatus(HttpStatus::BAD_REQUEST, $exc->getMessage());
            } finally {
                return $response;
            }
        }
    }

}
