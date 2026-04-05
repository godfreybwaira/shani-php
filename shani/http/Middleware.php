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
    use lib\http\HttpHeader;
    use lib\MediaType;
    use shani\advisors\SecurityMiddleware;

    final class Middleware
    {

        private readonly App $app;
        private readonly Event $listener;

        public function __construct(App &$app)
        {
            $this->app = $app;
            $this->listener = new Event(['before', 'after']);
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
         * Set client content type priority.
         * @return void
         */
        private function setProperContentType(): void
        {
            //1. extension 2. accept 3. content_type 4. default
            $ext = $this->app->request->route()->extension;
            if ($ext !== null) {
                $this->app->response->header()->addOne(HttpHeader::CONTENT_TYPE, MediaType::mime($ext));
                return;
            }
            $accepted = $this->app->request->header()->getOne(HttpHeader::ACCEPT, HttpHeader::CONTENT_TYPE);
            if ($accepted === '*/*' || $accepted === null) {
                $accepted = $this->app->platform() === 'web' ? MediaType::TEXT_HTML : MediaType::JSON;
            }
            $this->app->response->header()->addOne(HttpHeader::CONTENT_TYPE, MediaType::parse($accepted)[0]);
        }

        /**
         * Execute all registered middlewares according to their orders.
         * @param SecurityMiddleware $security Security middleware object
         * @return self
         */
        public function runWith(SecurityMiddleware $security): void
        {
            $this->setProperContentType();
            $security->setBrowsingPolicy();
            $security->preflightRequest();
            $this->app->on('web', function () use (&$security) {
                $security->addCspHeaders()->addResourceAccessPolicy()->csrfTest();
            });
            $security->authorized()->passedRequestMethodCheck();
            if ($this->listener->listening('before')) {
                $this->listener->trigger('before');
            }
            $this->app->processRequest();
            $this->listener->trigger('after');
        }
    }

}
