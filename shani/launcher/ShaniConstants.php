<?php

namespace shani\launcher {

    final class ShaniConstants
    {

        public static function define(string $root): void
        {
            /**
             * Server root directory
             */
            define('SHANI_SERVER_ROOT', $root);
            /**
             * Current timestamp according to RFC3339
             */
            define('SHANI_CURRENT_TIMESTAMP', date(DATE_RFC3339));
        }
    }

}