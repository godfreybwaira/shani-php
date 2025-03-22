<?php

/**
 * Description of SessionStorage
 *
 * @author coder
 */

namespace shani\persistence {

    use shani\http\App;

    final class SessionManager
    {

        private readonly App $app;
        private static array $session = [];
        private readonly Session $storage;

        public function __construct(App &$app)
        {
            $this->app = $app;
            $name = $app->request->uri->hostname;
            self::$session[$name] ??= new Session($app->config->sessionSavePath());
            $this->storage = self::$session[$name];
            $this->storage->start($app);
        }

        public function cart(string $name): SessionCart
        {
            return $this->storage->cart($name);
        }

        /**
         * Delete the current session and it's data.
         *
         * @return bool True on success, false otherwise
         */
        public function stop(): bool
        {
            return $this->storage->clear();
        }

        /**
         * Whether this session is expired.
         *
         * @return bool
         */
        public function expired(): bool
        {
            $session = $this->storage->getOne();
            if ($session == null) {
                return false;
            }
            $age = $this->app->config->cookieMaxAge()->getTimestamp();
            return time() - $session['lastActive'] > $age;
        }
    }

}