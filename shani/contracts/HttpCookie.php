<?php

/**
 * Create and manage HTTP Cookie
 * @author coder
 *
 * Created on: Mar 27, 2024 at 1:44:06 PM
 */

namespace shani\contracts {

    interface HttpCookie
    {

        /**
         * Gets the name of the cookie.
         *
         * @return string
         */
        public function name(): string;

        /**
         * Gets the value of the cookie.
         *
         * @return string
         */
        public function value(): string;
    }

}
