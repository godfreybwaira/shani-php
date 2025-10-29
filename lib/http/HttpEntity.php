<?php

/**
 * Description of HttpEntity
 * @author coder
 *
 * Created on: Feb 28, 2025 at 11:54:08 PM
 */

namespace lib\http {

    use lib\ds\map\ReadableMap;

    abstract class HttpEntity
    {

        public readonly string $protocol;
        protected readonly HttpHeader $headers;

        /**
         * HTTP cookies
         * @var ReadableMap
         */
        public readonly ReadableMap $cookie;

        protected function __construct(HttpHeader $headers, ReadableMap $cookies, string $protocol)
        {
            $this->headers = $headers;
            $this->protocol = $protocol;
            $this->cookie = $cookies;
        }

        /**
         * Get HTTP protocol version number
         * @return float
         */
        public function protocolVersion(): float
        {
            return (float) explode('/', $this->protocol)[1];
        }

        /**
         * Get HTTP header object
         * @return HttpHeader
         */
        public function header(): HttpHeader
        {
            return $this->headers;
        }
    }

}
