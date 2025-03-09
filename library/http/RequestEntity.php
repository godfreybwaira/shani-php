<?php

/**
 * Description of RequestEntity
 * @author coder
 *
 * Created on: Feb 28, 2025 at 11:44:48 PM
 */

namespace library\http {

    use library\Map;
    use library\URI;
    use library\MediaType;
    use shani\http\RequestRoute;
    use shani\http\UploadedFile;

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
        public readonly string $method;

        /**
         * User IP address
         * @var string
         */
        public readonly string $ip;
        public readonly array $files;
        private RequestRoute $route;
        private array $acceptedType;
        private readonly array $cookies, $body, $queries;

        public function __construct(
                URI $uri, HttpHeader $headers, array $body, array $cookies, array $files,
                string $method, string $ip, int $time, array $queries, string $protocol
        )
        {
            parent::__construct($headers, $protocol);
            $this->acceptedType = [];
            $this->cookies = $cookies;
            $this->queries = $queries;
            $this->method = $method;
            $this->files = $files;
            $this->body = $body;
            $this->time = $time;
            $this->uri = $uri;
            $this->ip = $ip;
            $this->changeRoute($uri->path());
        }

        /**
         * Check if the request is from local machine
         * @return bool True on success, false otherwise
         */
        public function localhost(): bool
        {
            return $this->ip === '127.0.0.1';
        }

        public function changeRoute(string $path): self
        {
            $this->route = new RequestRoute($this->method, $path);
            return $this;
        }

        public function route(): RequestRoute
        {
            return $this->route;
        }

        /**
         * Check if HTTP user agent accept the given content type.
         * @param string $type MIME type or last part of MIME before /
         * @return bool True on success, false otherwise.
         */
        public function accept(string $type): bool
        {
            if (empty($this->acceptedType)) {
                $this->acceptedType = MediaType::parse($this->headers->get(HttpHeader::ACCEPT));
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

        public function withUri(URI $uri): self
        {
            $copy = clone $this;
            $copy->uri = $uri;
            return $copy;
        }

        /**
         * Get uploaded file by name and optional file index
         * @param string $name Name value as given in upload form
         * @param int $index File index in array, default is zero
         * @return UploadedFile|null Return uploaded file object if file is valid
         * and exists, false otherwise.
         */
        public function file(string $name, int $index = null): ?UploadedFile
        {
            if ($index === null) {
                return $this->files[$name] ?? null;
            }
            return $this->files[$name][$index] ?? null;
        }

        /**
         * Get HTTP request values obtained via HTTP request body.
         * @param string|array $names named key
         * @param bool $selected If set to true, only the selected values will be returned.
         * @return type
         */
        public function body(string|array $names = null, bool $selected = true)
        {
            return Map::get($this->body, $names, $selected);
        }

        /**
         * Get request parameters sent via HTTP request endpoint
         * @param int|array $index Index of request parameter
         * @param bool $selected If set to true, only the selected values will be returned.
         * @return type
         */
        public function params(int|array $index = null, bool $selected = true)
        {
            return Map::get($this->route->params, $index, $selected);
        }

        public function withFile(string $name, UploadedFile $file): self
        {
            $copy = clone $this;
            $copy->files[$name] = $file;
            return $copy;
        }

        /**
         * Get HTTP queries
         * @param string|array $names query string name
         * @param bool $selected If set to true, only the selected values will be returned.
         * @return type
         */
        public function query(string|array $names = null, bool $selected = true)
        {
            return Map::get($this->queries, $names, $selected);
        }
    }

}
