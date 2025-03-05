<?php

/**
 * Description of RequestEntityBuilder
 * @author coder
 *
 * Created on: Mar 4, 2025 at 8:59:57 AM
 */

namespace library {

    use shani\engine\http\bado\RequestEntity;

    final class RequestEntityBuilder
    {

        private int $time = 0;
        private ?URI $uri = null;
        private ?HttpHeader $headers = null;
        private array $cookies = [], $files = [];
        private ?string $body, $method = null, $protocol = null;

        public function body(?string $body): self
        {
            $this->body = $body;
            return $this;
        }

        public function headers(HttpHeader $headers): self
        {
            $this->headers = $headers;
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
                $this->cookies = $cookies;
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

        public function type(string $type): self
        {
            $this->type = $type;
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
                    cookies: $this->cookies, files: $this->files, type: $this->type,
                    ip: $this->ip, time: $this->time
            );
        }
    }

}
