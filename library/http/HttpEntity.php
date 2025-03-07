<?php

/**
 * Description of HttpEntity
 * @author coder
 *
 * Created on: Feb 28, 2025 at 11:54:08 PM
 */

namespace library\http {

    use library\Map;
    use shani\contracts\HttpCookie;

    abstract class HttpEntity
    {

        private readonly string $protocol;
        protected readonly HttpHeader $headers;
        private array $cookies = [];

        protected function __construct(HttpHeader $headers, string $protocol)
        {
            $this->headers = $headers;
            $this->protocol = $protocol;
        }

        public function protocol(): string
        {
            return $this->protocol;
        }

        public function protocolVersion(): float
        {
            return (float) explode('/', $this->protocol)[1];
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
         * Get HTTP cookie value(s)
         * @param string|array $names named key
         * @param bool $selected If set to true, only the selected values will be returned.
         * @return type
         */
        public function cookies(string|array $names = null, bool $selected = true)
        {
            return Map::get($this->cookies, $names, $selected);
        }
    }

}
