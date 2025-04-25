<?php

/**
 * Description of SessionStorage
 *
 * @author coder
 */

namespace shani\persistence\session {

    use shani\http\App;
    use lib\http\HttpCookie;

    final class SessionManager
    {

        /**
         * Session storage object
         * @var SessionStorage
         */
        public readonly SessionStorage $storage;
        private readonly App $app;
        private readonly string $filepath;
        private readonly \DateTimeInterface $age;

        public function __construct(App &$app)
        {
            $this->app = $app;
            $this->age = $app->config->cookieMaxAge();
            $this->filepath = $this->createSavePath();
            if ($this->filepath !== null && is_file($this->filepath)) {
                $content = file_get_contents($this->filepath);
                $this->storage = SessionStorage::fromJson($content);
            } else {
                $now = time();
                $this->storage = new SessionStorage($now, $now);
            }
        }

        /**
         * Persist session data. You do not have to call this function as it is
         * called automatically when response is sent to client.
         * @return self
         */
        public function save(): self
        {
            if ($this->filepath !== null) {
                file_put_contents($this->filepath, $this->storage);
                chmod($this->filepath, 0600);
            }
            return $this;
        }

        private function createSavePath(): ?string
        {
            if (!$this->app->config->sessionEnabled()) {
                return null;
            }
            $path = $this->app->config->sessionSavePath();
            $name = $this->app->config->sessionName();
            $oldId = $this->app->request->cookie->getOne($name);
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
            if ($this->filepath !== null && is_file($this->filepath)) {
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
            $cookie = (new HttpCookie())->setHttpOnly(true)
                    ->setName($name)->setValue($sessId)
                    ->setSecure($this->app->request->uri->secure())
                    ->setDomain($this->app->request->uri->hostname)
                    ->setMaxAge($this->age);
            $this->app->response->header()->setCookie($cookie);
        }
    }

}