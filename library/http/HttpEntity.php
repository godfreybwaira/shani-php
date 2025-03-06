<?php

/**
 * Description of HttpEntity
 * @author coder
 *
 * Created on: Feb 28, 2025 at 11:54:08 PM
 */

namespace library\http {

    use shani\contracts\HttpCookie;

    abstract class HttpEntity
    {

        protected readonly HttpHeader $headers;
        private array $cookies = [];

        protected function __construct(HttpHeader $headers)
        {
            $this->headers = $headers;
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

        /**
         * Get HTTP header object
         * @return HttpHeader
         */
        public function header(): HttpHeader
        {
            return $this->headers;
        }

        /**
         * Get available HTTP cookie(s)
         * @return array HTTP Cookie(s)
         */
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
