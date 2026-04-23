<?php

/**
 * Description of MiddlewareHandlerInterface
 * @author goddy
 *
 * Created on: Apr 23, 2026 at 8:52:13 AM
 */

namespace features\middleware {

    interface MiddlewareHandlerInterface
    {

        /**
         * Run middlewares before the request is being processed (hit a controller).
         * Here is where you can even change the request object.
         * @return void
         */
        public function preRequest(): void;

        /**
         * Run middlewares before the response is being sent. Here is where you
         * can even change the response object.
         * @return void
         */
        public function preResponse(): void;

        /**
         * Run middlewares after the response is being sent. Here you can do all
         * your "heavy" tasks (tasks that take more than 50ms)
         * @return void
         */
        public function afterResponse(): void;
    }

}
