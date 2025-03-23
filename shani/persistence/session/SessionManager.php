<?php

/**
 * Description of SessionStorage
 *
 * @author coder
 */

namespace shani\persistence\session {

    use lib\Cookie;
    use shani\http\App;

    final class SessionManager
    {

        private readonly App $app;
        private readonly string $filepath;
        private readonly Session $storage;

        public function __construct(App &$app)
        {
            $this->app = $app;
            $this->filepath = $this->start();
            if (is_file($this->filepath)) {
                $content = file_get_contents($this->filepath);
                $this->storage = Session::fromJson($content);
            } else {
                $now = time();
                $this->storage = new Session($now, $now);
            }
        }

        public function save()
        {
            file_put_contents($this->filepath, $this->storage);
        }

        private function start(): string
        {
            $path = $this->app->config->sessionSavePath();
            $name = $this->app->config->sessionName();
            $oldId = $this->app->request->cookies($name);
            if ($oldId !== null && $this->app->config->isAsync()) {
                return $path . '/' . $oldId;
            }
            $newId = sha1(random_bytes(random_int(20, 70)));
            if ($oldId != null && is_file($path . '/' . $oldId)) {
                rename($path . '/' . $oldId, $path . '/' . $newId);
            }
            $cookie = (new Cookie())->setHttpOnly(true)->setName($name)
                    ->setValue($newId)->setSecure($this->app->request->uri->secure())
                    ->setDomain($this->app->request->uri->hostname)
                    ->setMaxAge($this->app->config->cookieMaxAge());
            $this->app->response->setCookie($cookie);
            return $path . '/' . $newId;
        }

        public function cart(string $name): Cart
        {
            return $this->storage->cart($name);
        }

        #[\Override]
        public function __toString()
        {
            return $this->storage;
        }

        /**
         * Delete the current session and it's data.
         * @return void
         */
        public function stop(): void
        {
            $this->storage->clear();
        }

        /**
         * Whether this session is expired.
         * @return bool
         */
        public function expired(): bool
        {
            $lastActive = $this->storage->getLastActive();
            $age = $this->app->config->cookieMaxAge()->getTimestamp();
            return time() - $lastActive > $age;
        }
    }

}