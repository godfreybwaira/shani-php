<?php

/**
 * Description of FileSessionStorage
 * @author goddy
 *
 * Created on: Apr 5, 2026 at 6:58:47 PM
 */

namespace shani\persistence\session {

    use lib\ds\map\MutableMap;
    use lib\http\HttpSameSite;
    use shani\http\App;

    final class FileSessionStorage extends SessionStorage
    {

        private readonly App $app;
        private bool $closed = false;

        public function __construct(App $app)
        {
            $this->app = $app;
            $this->start();
        }

        public function cart(string $cartName): MutableMap
        {
            return $this->createCart($cartName, $_SESSION[$cartName] ?? []);
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
    }

}
