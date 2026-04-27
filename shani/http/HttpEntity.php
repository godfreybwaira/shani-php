<?php

/**
 * Description of HttpEntity
 * @author coder
 *
 * Created on: Feb 28, 2025 at 11:54:08 PM
 */

namespace shani\http {

    use features\ds\map\ReadableMap;

    /**
     * HttpEntity is an abstract base class representing common HTTP entity data.
     * It encapsulates protocol information, headers, and cookies shared across
     * request and response entities.
     */
    abstract class HttpEntity
    {

        /**
         * HTTP protocol string (e.g., "HTTP/1.1")
         * @var string
         */
        public readonly string $protocol;

        /**
         * HTTP headers object
         * @var HttpHeader
         */
        protected readonly HttpHeader $headers;

        /**
         * HTTP cookies
         * @var ReadableMap
         */
        public readonly ReadableMap $cookie;

        /**
         * Construct a new HttpEntity
         *
         * @param HttpHeader $headers HTTP headers
         * @param ReadableMap $cookies HTTP cookies
         * @param string $protocol Protocol string (e.g., "HTTP/1.1")
         */
        protected function __construct(HttpHeader $headers, ReadableMap $cookies, string $protocol)
        {
            $this->headers = $headers;
            $this->protocol = $protocol;
            $this->cookie = $cookies;
        }

        /**
         * Get HTTP protocol version number
         *
         * @return float Protocol version (e.g., 1.1, 2.0)
         */
        public function protocolVersion(): float
        {
            return (float) explode('/', $this->protocol)[1];
        }

        /**
         * Get HTTP header object
         *
         * @return HttpHeader HTTP headers
         */
        public function header(): HttpHeader
        {
            return $this->headers;
        }
    }

}
