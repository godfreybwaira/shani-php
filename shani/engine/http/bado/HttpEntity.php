<?php

/**
 * Description of HttpEntity
 * @author coder
 *
 * Created on: Feb 28, 2025 at 11:54:08 PM
 */

namespace shani\engine\http\bado {

    use library\HttpHeader;
    use shani\contracts\HttpCookie;

    abstract class HttpEntity
    {

        protected readonly HttpHeader $headers;
        protected ?string $body;
        private array $cookies = [];

        protected function __construct(HttpHeader $headers, ?string $body)
        {
            $this->headers = $headers;
            $this->body = $body;
        }

        public function size(): int
        {
            return $this->bodySize() + $this->headers->size();
        }

        public function withHeaders(HttpHeader $headers): self
        {
            $copy = clone $this;
            $copy->headers = $headers;
            return $copy;
        }

        public function withCookies(HttpCookie $cookie): self
        {
            $copy = clone $this;
            $copy->cookies[$cookie->name()] = $cookie;
            return $copy;
        }

        public function body(): ?string
        {
            return $this->body;
        }

        public function bodySize(): int
        {
            return $this->body === null ? 0 : mb_strlen($this->body);
        }

        public function header(): HttpHeader
        {
            return $this->headers;
        }

        public function cookies(): array
        {
            return $this->cookies;
        }

        public abstract function protocolVersion(): float;

        public abstract function protocolLine(): string;

        public abstract function httpVersion(): string;

        public abstract function mediaType(): string;

        public abstract function protocol(): string;

        /**
         * Get HTTP response data type
         * @return string HTTP response data type
         */
        public abstract function type(): string;
    }

}
