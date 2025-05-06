<?php

/**
 * Represent Http Servers supported by this framework
 * @author coder
 *
 * Created on: May 6, 2025 at 1:41:52 PM
 */

namespace shani\contracts {

    interface SupportedHttpServer
    {

        /**
         * Start Http server
         * @param callable $callback A callback to run when a server started
         * @return void
         */
        public function start(callable $callback): void;

        /**
         * Stop (shutdown) Http server
         * @return void
         */
        public function stop(): void;
    }

}
