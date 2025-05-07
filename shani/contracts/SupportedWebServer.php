<?php

/**
 * Represent Web Servers supported by this framework (i.e HTTP servers)
 * @author coder
 *
 * Created on: May 6, 2025 at 1:41:52 PM
 */

namespace shani\contracts {

    interface SupportedWebServer
    {

        /**
         * Start a web server
         * @param callable $callback A callback to run when a server started
         * @return void
         */
        public function start(callable $callback): void;

        /**
         * Stop (shutdown) a web server
         * @return void
         */
        public function stop(): void;
    }

}
