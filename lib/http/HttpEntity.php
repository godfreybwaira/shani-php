<?php

/**
 * Description of HttpEntity
 * @author coder
 *
 * Created on: Feb 28, 2025 at 11:54:08 PM
 */

namespace lib\http {

    abstract class HttpEntity
    {

        public readonly string $protocol;
        protected readonly HttpHeader $headers;

        protected function __construct(HttpHeader $headers, string $protocol)
        {
            $this->headers = $headers;
            $this->protocol = $protocol;
        }

        /**
         * Get HTTP protocol version number
         * @return float
         */
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
