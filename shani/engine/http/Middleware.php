<?php

/**
 * Middleware registration point
 * @author coder
 *
 * Created on: Feb 13, 2024 at 8:55:03 AM
 */

namespace shani\engine\http {

    use library\Event;

    final class Middleware
    {

        private Event $listener;

        public function __construct(App &$app)
        {
            $this->listener = new Event(['before', 'after']);
            $this->listener->done(function (string $event) use (&$app) {
                if ($event !== 'before') {
                    return;
                }
                if ($app->response()->statusCode() < 300) {
                    return $app->submit();
                }
                $app->config()->handleHttpErrors();
            });
        }

        /**
         * A middleware registration function
         * @param string $event Event name can be either 'before' or 'after'.
         * Use 'before' event to register a callback for all middlewares that have
         * to be executed before the request is processed, and use 'after event to
         * register a callback for all middlewares that have to be executed after
         * the request is processed.
         * @param callable $callback A callback to be executed that will eventually
         * execute user middleware(s).
         * @return self
         */
        public function on(string $event, callable $callback): self
        {
            $this->listener->on($event, $callback);
            return $this;
        }

        /**
         * This method start execution of all registered middlewares according
         * to their orders.
         * @return self
         */
        public function run(): self
        {
            $this->listener->trigger('before')->trigger('after');
            return $this;
        }
    }

}
