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
    use lib\DataCompression;
    use lib\File;
    use lib\http\HttpCookie;
    use lib\http\HttpHeader;
    use lib\http\HttpStatus;
    use lib\http\RequestEntity;
    use lib\http\ResponseEntity;
    use lib\MediaType;
    use lib\RequestEntityBuilder;
    use lib\URI;
    use shani\core\Framework;

    final class HttpClient
    {

        private int $retries;
        private bool $asyncMode = true;
        private HttpHeader $requestHeader, $responseHeader;
        private ?DigitalSignature $signature = null;
        private ?DataCompression $compression = null;
        private ?Encryption $encryption = null;
        private ?string $headerName = null, $encoding = null;
        private readonly string $host;
        private array $files = [], $curlOptions;
        private string|array|null $body = null;
        private string $stream, $streamMode;
        private int $compressionMinSize = 0;

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
                CURLOPT_HEADER => false, CURLOPT_CONNECTTIMEOUT => $timeout,
                CURLOPT_UPLOAD_BUFFERSIZE => Framework::BUFFER_SIZE,
                CURLOPT_BUFFERSIZE => Framework::BUFFER_SIZE,
                CURLOPT_HEADERFUNCTION => function (\CurlHandle $curl, string $headerLine) {
                    return $this->collectResponseHeaders($headerLine);
                }
            ];
            $this->stream = 'php://temp';
            $this->streamMode = 'r+b';
            $this->requestHeader = new HttpHeader();
            $this->responseHeader = new HttpHeader();
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
            $copy->requestHeader = $header;
            return $copy;
        }

        /**
         * Set HTTP headers, overriding existing headers
         * @param HttpHeader $header
         * @return self
         */
        public function setHeader(HttpHeader $header): self
        {
            $this->requestHeader = $header;
            return $this;
        }

        /**
         * Clone existing request and return a new request but with new body.
         * @param string|array|null $body Request body
         * @return self
         */
        public function withBody(string|array|null $body): self
        {
            $copy = clone $this;
            $copy->body = $body;
            return $copy;
        }

        /**
         * Set request body
         * @param string|array|null $body Request body
         * @return self
         */
        public function setBody(string|array|null $body): self
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
        public function encrypt(Encryption $encryption): self
        {
            $this->encryption = $encryption;
            return $this;
        }

        /**
         * Send HTTP request using provided request method
         * @param string $method Request method e.g: GET, PUT, POST etc
         * @param string $endpoint Request destination endpoint
         * @param callable $callback A callback that accept ResponseEntity object
         * @return self
         */
        public function send(string $method, string $endpoint, callable $callback): self
        {
            if ($this->asyncMode) {
                Concurrency::parallel(fn() => $this->sendSync($method, $endpoint, $callback));
            } else {
                $this->sendSync($method, $endpoint, $callback);
            }
            return $this;
        }

        private function sendSync(string $method, string $endpoint, callable &$callback): void
        {
            if (isset($this->curlOptions[CURLOPT_INFILE])) {
                $this->setOptions([CURLOPT_INFILE => fopen($this->curlOptions[CURLOPT_INFILE], 'rb')]);
            }
            $this->createBody();
            $this->setOptions([CURLOPT_FILE => fopen($this->stream, $this->streamMode)]);
            $response = $this->getResponse($method, new URI($this->host . $endpoint));
            if ($this->stream === 'php://temp') {
                $response->setBody(stream_get_contents($this->curlOptions[CURLOPT_FILE], null, 0));
            }
            if ($this->encryption !== null) {
                $response->setBody($this->encryption->decrypt($response->body()));
            }
            if ($this->signature !== null) {
                $response = $this->verifySignature($response);
            }
            $this->decompress($response);
            $callback($response);
            if (isset($this->curlOptions[CURLOPT_INFILE])) {
                fclose($this->curlOptions[CURLOPT_INFILE]);
            }
            fclose($this->curlOptions[CURLOPT_FILE]);
        }

        private function getResponse(string $method, URI $endpoint): ResponseEntity
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
                CURLOPT_HTTPHEADER => $this->requestHeader->map(fn($name, $value) => $name . ':' . $value)->toArray()
            ]);
            curl_setopt_array($curl, $this->curlOptions);
            if (curl_exec($curl) === false) {
                throw new \Exception('Failed to execute command. ' . curl_error($curl));
            }
            $response = $this->prepareResponse($curl, $method, $endpoint);
            curl_close($curl);
            return $response;
        }

        public function prepareResponse(\CurlHandle &$curl, string $method, URI &$endpoint): ResponseEntity
        {
            $builder = (new RequestEntityBuilder())
                    ->headers($this->requestHeader)->files($this->files)
                    ->method($method)->uri($endpoint)
                    ->ip(curl_getinfo($curl, CURLINFO_LOCAL_IP))
                    ->protocol(curl_getinfo($curl, CURLINFO_PROTOCOL))
                    ->cookies($this->requestHeader->getCookies());
            if (is_array($this->body)) {
                $builder->body($this->body);
            } else {
                $builder->rawBody($this->body);
            }
            $status = HttpStatus::from(curl_getinfo($curl, CURLINFO_HTTP_CODE));
            return self::setReflectionHeaders($builder->build(), $status, $this->responseHeader);
        }

        private static function setReflectionHeaders(RequestEntity $request, HttpStatus $status, HttpHeader $header): ResponseEntity
        {
            $response = new ResponseEntity($request, $status, $header);
            $cookies = new HttpCookie($response->header()->getOne(HttpHeader::SET_COOKIE));
            foreach ($cookies as $cookie) {
                $response->request->header()->setCookie($cookie);
            }
            $etag = $response->header()->getOne(HttpHeader::ETAG);
            if ($etag !== null) {
                $response->request->header()->addOne(HttpHeader::IF_NONE_MATCH, $etag);
            }
            $lastModified = $response->header()->getOne(HttpHeader::LAST_MODIFIED);
            if ($lastModified !== null) {
                $response->request->header()->addOne(HttpHeader::IF_MODIFIED_SINCE, $lastModified);
            }
            return $response;
        }

        private function collectResponseHeaders(string $headerLine): int
        {
            $line = trim($headerLine);
            if (strpos($line, ':') > 0) {
                list($name, $value) = explode(':', $line, 2);
                $this->responseHeader->addOne(trim($name), trim($value));
            }
            return strlen($headerLine);
        }

        private function createBody(): void
        {
            $content = $this->formatBody();
            if ($content === null || $content === '') {
                return;
            }
            if ($this->compression !== null && $this->compressionMinSize < strlen($content)) {
                $content = DataCompression::compress($content, $this->encoding, $this->compression);
                $this->requestHeader->addOne(HttpHeader::CONTENT_ENCODING, $this->encoding);
            }
            if ($this->signature !== null) {
                $this->requestHeader->addOne($this->headerName, $this->signature->sign($content));
            }
            if ($this->encryption !== null) {
                $content = $this->encryption->encrypt($content);
            }
            $this->setOptions([CURLOPT_POSTFIELDS => $content]);
        }

        private function formatBody(): ?string
        {
            if (!empty($this->files)) {
                $files = [];
                foreach ($this->files as $name => $file) {
                    $files[$name] = new \CURLFile($file->path, $file->type, basename($file->path));
                }
                if (is_array($this->body)) {
                    return http_build_query(array_merge($this->body, $files));
                }
                return $this->body; //files will not be sent
            }
            if (is_array($this->body)) {
                return http_build_query($this->body);
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
         * Sign HTTP body with signature
         * @param DigitalSignature $signature DIgital signature object
         * @param string $headerName HTTP Header that will hold signature. Default is <code>X-Signature</code>
         * @return self
         */
        public function sign(DigitalSignature $signature, string $headerName = 'X-Signature'): self
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
         * @param callable $callback A callback that accept ResponseEntity object
         * @return self
         */
        public function get(string $endpoint, callable $callback): self
        {
            if ($this->body === null || empty($this->body)) {
                return $this->send('GET', $endpoint, $callback);
            }
            $connector = str_contains($endpoint, '?') ? '&' : '?';
            return $this->send('GET', $endpoint . $connector . http_build_query($this->body), $callback);
        }

        /**
         * Send HTTP POST request to destination
         * @param string $endpoint Request destination endpoint
         * @param callable $callback A callback that accept ResponseEntity object
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
         * @param callable $callback A callback that accept ResponseEntity object
         * @return self
         */
        public function put(string $endpoint, callable $callback): self
        {
            return $this->send('PUT', $endpoint, $callback);
        }

        /**
         * Send HTTP PATCH request to destination
         * @param string $endpoint Request destination endpoint
         * @param callable $callback A callback that accept ResponseEntity object
         * @return self
         */
        public function patch(string $endpoint, callable $callback): self
        {
            return $this->send('PATCH', $endpoint, $callback);
        }

        /**
         * Send HTTP DELETE request to destination
         * @param string $endpoint Request destination endpoint
         * @param callable $callback A callback that accept ResponseEntity object
         * @return self
         */
        public function delete(string $endpoint, callable $callback): self
        {
            return $this->send('DELETE', $endpoint, $callback);
        }

        /**
         * Send HTTP HEAD request to destination
         * @param string $endpoint Request destination endpoint
         * @param callable $callback A callback that accept ResponseEntity object
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
         * @param string $filename file name
         * @param callable $callback A callback that accept ResponseEntity object
         * @param callable $progress A callback for showing progress during download
         * where the first argument is total bytes to load and the second is
         * total bytes loaded
         * @return self
         */
        public function download(string $endpoint, string $destination, string $filename, callable $callback, callable $progress = null): self
        {
            $this->stream = $destination . '/' . $filename;
            $this->streamMode = 'a+b';
            if (is_readable($destination)) {
                $this->setOptions([CURLOPT_RESUME_FROM => filesize($destination)]);
            }
            if ($progress !== null) {
                $this->setOptions([
                    CURLOPT_NOPROGRESS => false,
                    CURLOPT_PROGRESSFUNCTION => function (&$curl, $total, $loaded) use (&$progress) {
                        $progress($total, $loaded);
                    }
                ]);
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
            $this->setOptions([
                CURLOPT_INFILE => $source, CURLOPT_UPLOAD => true,
                CURLOPT_INFILESIZE => filesize($source)
            ]);
            $this->requestHeader->addIfAbsent(HttpHeader::CONTENT_TYPE, MediaType::fromFilename($source));
            $this->requestHeader->setFilename(basename($source), false);
            if ($progress !== null) {
                $this->setOptions([
                    CURLOPT_NOPROGRESS => false,
                    CURLOPT_PROGRESSFUNCTION => function (&$curl, $dt, $dl, $total, $loaded) use (&$progress) {
                        $progress($total, $loaded);
                    }
                ]);
            }
            return $this->put($endpoint, $callback);
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

        /**
         * Encode request body using supported encoding algorithms supported by <code>DataCompression</code>
         * @param int $minSize Minimum body size that should not be encoded
         * @param string $algorithm Encoding algorithm
         * @param DataCompression $level Data compression object
         * @return self
         * @throws \Exception Throws exception if encoding algorithm is not supported.
         * @see DataCompression::algorithm()
         */
        public function setCompression(int $minSize, string $algorithm = 'gzip', DataCompression $level = null): self
        {
            $this->encoding = DataCompression::algorithm($algorithm);
            if ($this->encoding === null) {
                throw new \Exception('Encoding algorithm not supported');
            }
            $this->compressionMinSize = $minSize;
            $this->compression = $level ?? DataCompression::BEST;
            return $this;
        }

        private function decompress(ResponseEntity &$response): self
        {
            $encoding = $response->header()->getOne(HttpHeader::CONTENT_ENCODING);
            if ($encoding !== null) {
                $response->setBody(DataCompression::decompress($response->body(), $encoding));
            }
            return $this;
        }
    }

}
