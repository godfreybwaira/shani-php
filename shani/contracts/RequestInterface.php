<?php

/**
 * Description of RequestInterface
 * @author coder
 *
 * Created on: Aug 10, 2024 at 8:44:22 PM
 */

namespace shani\contracts {

    interface RequestInterface
    {

        /**
         * Get HTTP request method
         * @return string HTTP request method
         */
        public function method(): string;

        /**
         * Get HTTP cookie value(s)
         * @param string|array $names named key
         * @param bool $selected If set to true, only the selected values will be returned.
         * @return array|string|null
         */
        public function cookies(string|array $names = null, bool $selected = true): array|string|null;

        /**
         * Get HTTP request values obtained via HTTP request body.
         * @param string|array $names named key
         * @param bool $selected If set to true, only the selected values will be returned.
         * @return array|string|null
         */
        public function body(string|array $names = null, bool $selected = true): array|string|null;

        /**
         * Get HTTP queries
         * @param string|array $names query string name
         * @param bool $selected If set to true, only the selected values will be returned.
         * @return array|string|null
         */
        public function query(string|array $names = null, bool $selected = true): array|string|null;

        /**
         * Get HTTP request headers
         * @param string|array $names header name
         * @param bool $selected If set to true, only the selected values will be returned.
         * @return array|string|null
         */
        public function headers(string|array $names = null, bool $selected = true): array|string|null;

        /**
         * Get the original unchanged request URI object
         * @return \library\URI Request URI object
         */
        public function uri(): \library\URI;
    }

}
