<?php

/**
 * Description of PersistentSessionStorage
 * @author goddy
 *
 * Created on: Apr 3, 2026 at 7:27:49 PM
 */

namespace features\session {

    use features\ds\map\MutableMap;
    use shani\http\enums\HttpSameSite;
    use shani\launcher\App;

    final class PersistentSessionStorage implements SessionStorageInterface
    {

        private readonly App $app;
        private array $carts = [];
        private bool $closed = false;
        private readonly SessionConnectionInterface $conn;

        private function __construct(App $app, SessionConnectionInterface $conn)
        {
            $this->conn = $conn;
            $this->app = $app;
            $this->start();
        }

        public function cartExists(string $cartName): bool
        {
            return isset($this->carts[$cartName]);
        }

        public function cart(string $cartName): MutableMap
        {
            if (!isset($this->carts[$cartName])) {
                $this->carts[$cartName] = new MutableMap($_SESSION[$cartName] ?? []);
            }
            return $this->carts[$cartName];
        }

        public function close(): void
        {
            if (!$this->closed) {
                foreach ($this->carts as $name => $cart) {
                    $_SESSION[$name] = $cart->toArray();
                }
                $this->closed = true;
            }
        }

        public function destroy(): void
        {
            unset($this->carts);
            session_unset();
            session_destroy();
        }

        public function refresh(): SessionStorageInterface
        {
            if (!$this->app->config->isAsync()) {
                session_regenerate_id(true);
            }
            return $this;
        }

        public function started(): bool
        {
            return session_status() === PHP_SESSION_ACTIVE;
        }

        private function start(): void
        {
            if (session_status() === PHP_SESSION_NONE) {
                session_start([
                    'save_handler' => $this->conn->getHandler(),
                    'save_path' => $this->conn->getConnectionString(),
                    'name' => $this->app->config->sessionName(),
                    'use_strict_mode' => true,
                    'use_cookies' => true,
                    'cookie_path' => '/',
                    'cookie_lifetime' => 0,
                    'cookie_httponly' => true,
                    'cookie_secure' => $this->app->request->uri->secure(),
                    'cookie_samesite' => HttpSameSite::LAX->value,
                    'cookie_domain' => $this->getCookieDomain()
                ]);
                register_shutdown_function([$this, 'close']);
            }
        }

        private function getCookieDomain(): string
        {
            return '.' . ltrim($this->app->request->uri->hostname(), 'www.');
        }

        public static function getStorage(App $app, ?SessionConnectionInterface $conn): SessionStorageInterface
        {
            if ($conn === null) {
                return new MemorySessionStorage();
            }
            return new PersistentSessionStorage($app, $conn);
        }
    }

}