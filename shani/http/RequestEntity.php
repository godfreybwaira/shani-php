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
    use shani\config\PathConfig;
    use shani\http\RequestRoute;

    /**
     * RequestEntity represents an HTTP request entity with its URI, headers,
     * body, cookies, files, method, IP, and other metadata.
     *
     * It provides helper methods to access request data, manipulate the body,
     * verify signatures, decompress content, and check accepted media types.
     */
    final class RequestEntity extends HttpEntity
    {

        /**
         * Original unchanged request URI object
         * @var URI
         */
        public readonly URI $uri;

        /**
         * User request time (Unix timestamp)
         * @var int
         */
        public readonly int $time;

        /**
         * HTTP Request method (GET, POST, etc.)
         * @var string
         */
        public readonly string $method;

        /**
         * User IP address
         * @var string|null
         */
        public readonly ?string $ip;

        /** @var string|null Raw request body */
        private ?string $raw;

        /** @var RequestRoute Current request route */
        private RequestRoute $route;

        /** @var array[File] Uploaded files */
        public readonly array $files;

        /** @var string Whether request originated from localhost */
        public readonly string $localhost;

        /** @var array|null Cached accepted content types */
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

        /**
         * Construct a new RequestEntity
         *
         * @param URI $uri Request URI
         * @param HttpHeader $headers HTTP headers
         * @param ReadableMap $body Request body
         * @param ReadableMap $cookies Request cookies
         * @param array[File] $files Uploaded files
         * @param string $method HTTP method
         * @param string $ip Client IP address
         * @param int $time Request timestamp
         * @param ReadableMap $queries Query parameters
         * @param string $protocol Protocol used (HTTP/HTTPS)
         * @param string|null $rawBody Raw request body (optional)
         */
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

        /**
         * Replace raw body with new content
         * @param string $body Raw body content
         * @return self
         */
        public function withRawBody(string $body): self
        {
            $this->raw = $body;
            return $this;
        }

        /**
         * Replace request body with new map
         * @param ReadableMap $body New body
         * @return self
         */
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
         * @return array Users accepted languages
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
         * Check if HTTP user agent accepts the given content type.
         * @param string $type MIME type or last part of MIME before /
         * @return bool True if accepted, false otherwise
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
         * @param int|null $index File index in array, default is null
         * @return File|null Uploaded file object if exists, null otherwise
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
         * @return string|null Parameter value or null
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
         * @param string $headerName Header name that holds signature
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
         * Decrypt request body with the given encryption keys
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

        /**
         * Checks if this is an active static asset request.
         *
         * @param PathConfig $config Path configuration
         *
         * @return bool True if it is a static asset request, false otherwise.
         */
        public function isStaticResource(PathConfig $config): bool
        {
            $prefix = '/' . $this->route->module;
            return $prefix === $config->privateBucket || $prefix === $config->publicBucket;
        }

        /**
         * Checks if the current request is for a public resource.
         *
         * @param PathConfig $config Path configuration
         *
         * @return bool True if public, false otherwise.
         */
        public function isPublicResource(PathConfig $config): bool
        {
            $prefix = '/' . $this->route->module;
            return $prefix === $config->publicBucket;
        }
    }

}
