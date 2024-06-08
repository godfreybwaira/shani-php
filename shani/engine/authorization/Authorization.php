<?php

/**
 * Description of Authorization
 * @author coder
 *
 * Created on: Jun 4, 2024 at 11:24:22 AM
 */

namespace shani\engine\authorization {

    use shani\engine\http\App;
    use shani\engine\http\Session;

    final class Authorization
    {

        public const AUTH_SESSION = 0, AUTH_JWT = 1;
        private const NAME = '_mGnUs$nrWM0';

        private Session $session;
        private App $app;
        private ?string $permissions;
        private bool $valid = false;

        public function __construct(App &$app)
        {
            $this->app = $app;
            $this->extractTokenData();
            $this->session = new Session(self::NAME);
        }

        private function extractTokenData(): void
        {
            if ($this->app->config()->authorizationType() !== self::AUTH_JWT) {
                return;
            }
            $tokenString = $this->app->request()->headers('authorization');
            if ($tokenString === null) {
                return;
            }
            if (JWT::verify($tokenString, $this->app->config()->tokenSecretKey())) {
                $this->setPermission(array_values(JWT::extract($tokenString))[0]);
            }
        }

        public function verified(): bool
        {
            return $this->valid || $this->session->get('validUser') === true;
        }

        public function setPermission(string $list): self
        {
            $this->valid = true;
            $this->permissions = $list;
            $this->session->put(['validUser' => $this->valid, 'permission' => $this->permissions]);
            return $this;
        }

        public function hasPermission(string $url): bool
        {
            $list = $this->permissions ?? $this->session->get('permission');
            if ($list === null) {
                return false;
            }
            return $list === '*' || preg_match('/\b' . \library\Utils::digest($url) . '\b/', $list);
        }
    }

}
