<?php

/**
 * Description of SessionConnectionInterface
 * @author goddy
 *
 * Created on: Apr 6, 2026 at 12:08:05 PM
 */

namespace features\persistence\session {

    interface SessionConnectionInterface
    {

        /**
         * Create and return connection string
         * @return string
         */
        public function getConnectionString(): string;

        /**
         * Get session save handler
         * @return string Handler name
         */
        public function getHandler(): string;
    }

}
