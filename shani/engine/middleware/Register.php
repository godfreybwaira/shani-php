<?php

/**
 * Description of Register
 * @author coder
 *
 * Created on: Feb 13, 2024 at 8:55:03 AM
 */

namespace shani\engine\middleware {

    use library\Event;

    final class Register
    {

        private Event $listener;
        private \shani\engine\http\App $app;

        public function __construct(\shani\engine\http\App &$app, callable $done)
        {
            $this->listener = new Event(['before', 'after']);
            $this->listener->done($done);
            $this->app = $app;
        }

        public function on(string $event, callable $callback): self
        {
            $this->listener->on($event, $callback);
            return $this;
        }

        public function before(): self
        {
            $this->on('before', fn() => Security::checkAuthorization($this->app));
            $this->on('before', fn() => Security::checkAuthentication($this->app));
            $this->on('before', fn() => Security::blockCSRF($this->app));
            return $this;
        }

        public function after(): self
        {
            return $this;
        }

        public function run(): self
        {
            $this->listener->trigger('before')->trigger('after');
            return $this;
        }
    }

}
