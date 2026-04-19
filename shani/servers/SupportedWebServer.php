<?php

/**
 * Represent Web Servers supported by this framework (i.e HTTP servers)
 * @author coder
 *
 * Created on: May 6, 2025 at 1:41:52 PM
 */

namespace shani\servers {


    interface SupportedWebServer
    {

        /**
         * Called when a web server receive new request
         * @param \Closure $callback A callback to run when a new request is received
         * i.e <code>$callback(RequestEntity $request, ResponseWriter $writer):void</code>
         * @return SupportedWebServer
         */
        public function request(\Closure $callback): SupportedWebServer;

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
