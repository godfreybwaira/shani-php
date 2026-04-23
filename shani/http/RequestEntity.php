<?php

/**
 * Description of RequestEntity
 * @author coder
 *
 * Created on: Feb 28, 2025 at 11:44:48 PM
 */

namespace shani\http {

    use features\crypto\DigitalSignature;
    use features\crypto\Encryption;
    use features\ds\map\ReadableMap;
    use features\utils\DataCompression;
    use features\utils\File;
    use features\utils\MediaType;
    use features\utils\URI;
    use shani\http\RequestRoute;

    final class RequestEntity extends HttpEntity
    {

        /**
         * Original unchanged request URI object
         * @var URI
         */
        public readonly URI $uri;

        /**
         * User request time
         * @var int
         */
        public readonly int $time;

        /**
         * HTTP Request method
         * @var string
         */
        public readonly string $method;

        /**
         * User IP address
         * @var string|null
         */
        public readonly ?string $ip;
        private ?string $raw;
        private RequestRoute $route;
        public readonly array $files;
        public readonly string $localhost;
        private ?array $acceptedType = null;

        /**
         * Represents request body
         * @var ReadableMap
         */
        private ReadableMap $body;

        /**
         * Represents request Query string
         * @var ReadableMap
         */
        public readonly ReadableMap $query;

        public function __construct(
                URI $uri, HttpHeader $headers, ReadableMap $body, ReadableMap $cookies,
                array $files, string $method, string $ip, int $time, ReadableMap $queries,
                string $protocol, ?string $rawBody = null
        )
        {
            parent::__construct($headers, $cookies, $protocol);
            $this->localhost = $ip === '127.0.0.1';
            $this->changeRoute(RequestRoute::fromPath($uri->path()));
            $this->query = $queries;
            $this->method = $method;
            $this->raw = $rawBody;
            $this->files = $files;
            $this->body = $body;
            $this->time = $time;
            $this->uri = $uri;
            $this->ip = $ip;
        }

        public function withRawBody(string $body): self
        {
            $this->raw = $body;
            return $this;
        }

        public function withBody(ReadableMap $body): self
        {
            $this->body = $body;
            return $this;
        }

        /**
         * Get raw request body
         * @return string|null Raw input data or null
         */
        public function raw(): ?string
        {
            return $this->raw;
        }

        /**
         * Get request body
         * @return ReadableMap
         */
        public function body(): ReadableMap
        {
            return $this->body;
        }

        /**
         * Change existing route to a new route
         * @param RequestRoute $newRoute New Route
         * @return self
         */
        public function changeRoute(RequestRoute $newRoute): self
        {
            $this->route = $newRoute;
            return $this;
        }

        /**
         * Get HTTP request route
         * @return RequestRoute
         */
        public function route(): RequestRoute
        {
            return $this->route;
        }

        /**
         * Get user request language codes. These values will be used for selection
         * of application language if they are supported.
         * @return array users accepted languages
         */
        public function languages(): array
        {
            $acceptedLanguages = $this->headers->getOne(HttpHeader::ACCEPT_LANGUAGE);
            if ($acceptedLanguages !== null) {
                $langs = explode(',', $acceptedLanguages);
                return array_map(fn($val) => strtolower(trim(explode(';', $val)[0])), $langs);
            }
            return [];
        }

        /**
         * Check if HTTP user agent accept the given content type.
         * @param string $type MIME type or last part of MIME before /
         * @return bool True on success, false otherwise.
         */
        public function accepted(string $type): bool
        {
            if (empty($this->acceptedType)) {
                $this->acceptedType = MediaType::parse($this->headers->getOne(HttpHeader::ACCEPT));
            }
            if (!empty($this->acceptedType)) {
                if (str_contains($type, '/')) {
                    return in_array($type, $this->acceptedType);
                }
                foreach ($this->acceptedType as $mime) {
                    $idx = strpos($mime, '/') + 1;
                    if (substr($mime, $idx) === $type) {
                        return true;
                    }
                }
            }
            return false;
        }

        /**
         * Get uploaded file by name and optional file index
         * @param string $name Name value as given in upload form
         * @param int $index File index in array, default is zero
         * @return File|null Return uploaded file object if file is valid
         * and exists, false otherwise.
         */
        public function file(string $name, int $index = null): ?File
        {
            if ($index === null) {
                return $this->files[$name] ?? null;
            }
            return $this->files[$name][$index] ?? null;
        }

        /**
         * Get request parameters sent via HTTP request endpoint
         * @param int $index Index of a request parameter
         * @return string|null
         */
        public function params(int $index): ?string
        {
            return $this->route->params[$index] ?? null;
        }

        /**
         * Decompress raw request body
         * @return self
         */
        public function decompress(): self
        {
            if (!empty($this->raw)) {
                $encoding = $this->headers->getOne(HttpHeader::CONTENT_ENCODING);
                $this->raw = DataCompression::decompress($this->raw, $encoding);
            }
            return $this;
        }

        /**
         * Verify request raw body with provided digital signature
         * @param DigitalSignature|null $signature Digital signature object
         * @param string $headerName Header name that will hold signature
         * @return self
         */
        public function verify(?DigitalSignature $signature, string $headerName): self
        {
            if ($signature !== null && !empty($this->raw)) {
                $signature->verify($this->raw, $this->headers->getOne($headerName));
            }
            return $this;
        }

        /**
         * Encrypt response body with the given encryption keys
         * @param Encryption|null $encryption Encryption object
         * @return self
         */
        public function decrypt(?Encryption $encryption): self
        {
            if ($encryption !== null && !empty($this->raw)) {
                $this->raw = $encryption->decrypt($this->raw);
            }
            return $this;
        }
    }

}
