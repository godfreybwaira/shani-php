<?php

/**
 * Represent Web Servers supported by this framework (i.e HTTP servers)
 * @author coder
 *
 * @since May 6, 2025 at 1:41:52 PM
 */

namespace shani\servers {

    use shani\contracts\ConcurrencyInterface;
    use shani\contracts\EventHandler;

    interface SupportedWebServer
    {

        /**
         * Called when a web server receive new request
         * @param \Closure $callback A callback to run when a new request is received
         * i.e <code>$callback(RequestEntity $request, ResponseWriterInterface $writer):void</code>
         * @return void
         */
        public function request(\Closure $callback): void;

        /**
         * Return concurrency handler for a web server that supports concurrency
         * @return ConcurrencyInterface
         */
        public function getConcurrencyHandler(): ConcurrencyInterface;

        /**
         * Return event handler
         * @return EventHandler
         */
        public function getEventHandler(): EventHandler;
    }

}
