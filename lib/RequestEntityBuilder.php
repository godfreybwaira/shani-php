<?php

/**
 * Description of RequestEntityBuilder
 * @author coder
 *
 * Created on: Mar 4, 2025 at 8:59:57 AM
 */

namespace lib {

    use lib\http\HttpHeader;
    use lib\http\RequestEntity;
    use lib\ds\map\ReadableMap;

    final class RequestEntityBuilder
    {

        private int $time;
        private ?URI $uri;
        private array $files = [];
        private ?HttpHeader $headers;
        private ?string $method, $ip, $protocol, $rawBody;
        private ReadableMap $cookies, $queries, $body;

        public function __construct()
        {
            $this->time = time();
            $this->rawBody = $this->method = $this->ip = $this->protocol = null;
            $this->headers = new HttpHeader();
            $this->body = new ReadableMap();
            $this->queries = new ReadableMap();
            $this->cookies = new ReadableMap();
        }

        public function rawBody(?string $body): self
        {
            if (!empty($body)) {
                $this->rawBody = $body;
            }
            return $this;
        }

        public function body(?array $body): self
        {
            if (!empty($body)) {
                $this->body = new ReadableMap($body);
            }
            return $this;
        }

        public function query(?array $values): self
        {
            if (!empty($values)) {
                $this->queries = new ReadableMap($values);
            }
            return $this;
        }

        public function headers(?HttpHeader $headers): self
        {
            if (!empty($headers)) {
                $this->headers = $headers;
            }
            return $this;
        }

        public function uri(URI $uri): self
        {
            $this->uri = $uri;
            return $this;
        }

        public function method(string $method): self
        {
            $this->method = strtolower($method);
            return $this;
        }

        public function protocol(string $protocol): self
        {
            $this->protocol = $protocol;
            return $this;
        }

        public function cookies(?array $cookies): self
        {
            if (!empty($cookies)) {
                $this->cookies = new ReadableMap($cookies);
            }
            return $this;
        }

        public function files(?array $files): self
        {
            if (!empty($files)) {
                $this->files = $files;
            }
            return $this;
        }

        public function ip(string $ip): self
        {
            $this->ip = $ip;
            return $this;
        }

        public function time(int $time): self
        {
            $this->time = $time;
            return $this;
        }

        public function build(): RequestEntity
        {
            return new RequestEntity(
                    uri: $this->uri, headers: $this->headers, body: $this->body,
                    cookies: $this->cookies, files: $this->files, ip: $this->ip,
                    time: $this->time, queries: $this->queries, method: $this->method,
                    protocol: $this->protocol, rawBody: $this->rawBody
            );
        }
    }

}
