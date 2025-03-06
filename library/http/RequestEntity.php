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
    use shani\engine\http\RequestRoute;
    use shani\engine\http\UploadedFile;

    final class RequestEntity extends HttpEntity
    {

        private readonly URI $uri;

        /**
         * User request time
         * @var int
         */
        public readonly int $time;
        private array $acceptedType = [], $queries;
        public readonly string $type, $method;

        /**
         * User IP address
         * @var string
         */
        public readonly string $ip;
        private readonly array $files, $cookies;
        private readonly array $body;
        private RequestRoute $route;

        public function __construct(
                URI $uri, HttpHeader $headers, array $body, array $cookies, array $files,
                string $type, string $method, string $ip, int $time, array $queries
        )
        {
            parent::__construct($headers);
            $this->setRoute(new RequestRoute($uri->path()));
            $this->cookies = $cookies;
            $this->queries = $queries;
            $this->method = $method;
            $this->files = $files;
            $this->body = $body;
            $this->type = $type;
            $this->time = $time;
            $this->uri = $uri;
            $this->ip = $ip;
        }

        /**
         * Check if the request is from local machine
         * @return bool True on success, false otherwise
         */
        public function localhost(): bool
        {
            return $this->ip === '127.0.0.1';
        }

        public function setRoute(RequestRoute $route): self
        {
            $this->route = $route;
            return $this;
        }

        public function route(): RequestRoute
        {
            return $this->route;
        }

        #[\Override]
        public function httpVersion(): string
        {

        }

        #[\Override]
        public function mediaType(): string
        {

        }

        #[\Override]
        public function protocol(): string
        {

        }

        #[\Override]
        public function protocolLine(): string
        {

        }

        #[\Override]
        public function protocolVersion(): float
        {

        }

        #[\Override]
        public function type(): string
        {
            return $this->type;
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

        /**
         * Get the original unchanged request URI object
         * @return URI Request URI object
         * @see self::path()
         */
        public function uri(): URI
        {
            return $this->uri;
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
        public function file(string $name, int $index = 0): ?UploadedFile
        {
            return $this->files[$name][$index] ?? null;
        }

        /**
         * Get HTTP cookie value(s)
         * @param string|array $names named key
         * @param bool $selected If set to true, only the selected values will be returned.
         * @return type
         */
        public function cookie(string|array $names = null, bool $selected = true)
        {
            return Map::get($this->cookies, $names, $selected);
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

        public function withFile(UploadedFile $file): self
        {
            $copy = clone $this;
            $copy->files[$file->name][] = $file;
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
