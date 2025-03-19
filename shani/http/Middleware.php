<?php

/**
 * Middleware registration point. When a middleware check fails, it must set
 * HTTP status code above 299 for it to raise an error.
 * @author coder
 *
 * Created on: Feb 13, 2024 at 8:55:03 AM
 */

namespace shani\http {

    use lib\Event;
    use shani\advisors\SecurityMiddleware;

    final class Middleware
    {

        private readonly App $app;
        private readonly Event $listener;

        public function __construct(App &$app)
        {
            $this->app = $app;
            $this->listener = new Event(['before']);
            $this->listener->done(fn() => self::returnResponse($app));
        }

        private static function returnResponse(App &$app)
        {
            if ($app->response->status()->value < 300) {
                return $app->processRequest();
            }
            $app->config->errorHandler();
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
         * @param SecurityMiddleware $advisor Security advisor middleware object
         * @return self
         */
        public function runWith(SecurityMiddleware &$advisor): void
        {
            if ($this->app->config->authorizationDisabled()) {
                self::returnResponse($this->app);
                return;
            }
            $this->app->on('web', function () use (&$advisor) {
                $advisor->blockClickjacking()->resourceAccessPolicy();
                $advisor->preflightRequest()->csrfTest();
            });
            if ($advisor->authorized() && $advisor->passedRequestMethodCheck()) {
                if ($this->listener->listening('before')) {
                    $this->listener->trigger('before');
                    return;
                }
            }
            self::returnResponse($this->app);
        }
    }

}
