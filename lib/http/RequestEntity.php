<?php

/**
 * Description of RequestEntity
 * @author coder
 *
 * Created on: Feb 28, 2025 at 11:44:48 PM
 */

namespace lib\http {

    use lib\Map;
    use lib\MediaType;
    use lib\URI;
    use shani\contracts\HttpCookie;
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

        /**
         * Request method
         * @var string
         */
        public readonly string $method;

        /**
         * User IP address
         * @var string
         */
        public readonly string $ip;

        /**
         * Check whether the request comes from local machine
         * @var string
         */
        public readonly string $localhost;
        public readonly array $files;
        private RequestRoute $route;
        private ?array $acceptedType = null;
        private readonly array $cookies, $body, $queries;

        public function __construct(
                URI $uri, HttpHeader $headers, array $body, array $cookies, array $files,
                string $method, string $ip, int $time, array $queries, string $protocol
        )
        {
            parent::__construct($headers, $protocol);
            $this->localhost = $ip === '127.0.0.1';
            $this->method = $method;
            $this->changeRoute($uri->path);
            $this->cookies = $cookies;
            $this->queries = $queries;
            $this->files = $files;
            $this->body = $body;
            $this->time = $time;
            $this->uri = $uri;
            $this->ip = $ip;
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
         * Get user request language codes. These values will be used for selection
         * of application language if they are supported.
         * @return array users accepted languages
         */
        public function languages(): array
        {
            $acceptedLanguages = $this->headers->get(HttpHeader::ACCEPT_LANGUAGE);
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
         * @param string $name named key
         * @return string|null
         */
        public function body(string $name): ?string
        {
            return $this->body[$name] ?? null;
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

        public function withFile(string $name, UploadedFile $file): self
        {
            $copy = clone $this;
            $copy->files[$name] = $file;
            return $copy;
        }

        /**
         * Get HTTP queries
         * @param string $name query string name
         * @return string|null
         */
        public function query(string $name): ?string
        {
            return $this->queries[$name] ?? null;
        }

        /**
         * Get HTTP cookie value(s)
         * @param string|array $names named key
         * @param bool $selected If set to true, only the selected values will be returned.
         * @return type
         */
        public function cookies(string|array $names = null, bool $selected = true)
        {
            return Map::get($this->cookies, $names, $selected);
        }

        public function withCookies(HttpCookie $cookie): self
        {
            $copy = clone $this;
            $copy->cookies[$cookie->name()] = $cookie;
            return $copy;
        }
    }

}
