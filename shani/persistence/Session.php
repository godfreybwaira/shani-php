<?php

/**
 * Description of SessionStorage
 *
 * @author coder
 */

namespace shani\persistence {

    use shani\http\App;

    final class Session
    {

        private readonly App $app;
        private static array $session = [];
        private readonly SessionStorage $storage;

        public function __construct(App &$app)
        {
            $this->app = $app;
            $name = $app->request->uri->hostname;
            self::$session[$name] ??= new SessionStorage($app->config->sessionSavePath());
            $this->storage = self::$session[$name];
            $this->storage->start();
        }

        public function cart(string $name): SessionCart
        {
            return $this->storage->cart($name);
        }

        public function stop(): bool
        {
            return $this->storage->clear();
        }
    }

}