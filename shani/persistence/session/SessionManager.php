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

        /**
         * Session storage object
         * @var Session
         */
        public readonly Session $storage;
        private readonly App $app;
        private readonly string $filepath;
        private readonly \DateTimeInterface $age;

        public function __construct(App &$app)
        {
            $this->app = $app;
            $this->age = $app->config->cookieMaxAge();
            $this->filepath = $this->createSavePath();
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

        private function createSavePath(): string
        {
            $path = $this->app->config->sessionSavePath();
            $name = $this->app->config->sessionName();
            $oldId = $this->app->request->cookies($name);
            if ($oldId !== null && $this->app->config->isAsync()) {
                $this->sendCookie($name, $oldId);
                return $path . '/' . $oldId;
            }
            $newId = sha1(random_bytes(random_int(20, 70)));
            if (is_file($path . '/' . $oldId)) {
                rename($path . '/' . $oldId, $path . '/' . $newId);
            }
            $this->sendCookie($name, $newId);
            return $path . '/' . $newId;
        }

        /**
         * Delete the current session and it's data.
         * @return void
         */
        public function stop(): void
        {
            if (is_file($this->filepath)) {
                unlink($this->filepath);
            }
            $this->storage->clear();
        }

        /**
         * Whether this session is expired.
         * @return bool
         */
        public function expired(): bool
        {
            $lastActive = $this->storage->getLastActive();
            return time() - $lastActive > $this->age->getTimestamp();
        }

        private function sendCookie(string $name, string $sessId): void
        {
            $cookie = (new Cookie())->setHttpOnly(true)
                    ->setName($name)->setValue($sessId)
                    ->setSecure($this->app->request->uri->secure())
                    ->setDomain($this->app->request->uri->hostname)
                    ->setMaxAge($this->age);
            $this->app->response->setCookie($cookie);
        }
    }

}