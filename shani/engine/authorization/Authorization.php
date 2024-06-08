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
        private bool $valid = false, $isjwt = true;

        public function __construct(App &$app)
        {
            $this->app = $app;
            $this->isjwt = $app->config()->authorizationType() === self::AUTH_JWT;
            $this->extractTokenData();
            $this->session = new Session(self::NAME);
        }

        private function extractTokenData(): void
        {
            if (!$this->isjwt) {
                return;
            }
            $tokenString = $this->app->request()->headers('authorization');
            if ($tokenString === null) {
                return;
            }
            if (JWT::verify($tokenString, $this->app->config()->signatureSecretKey())) {
                $this->setPermission(array_values(JWT::extract($tokenString))[0]);
            } else {
                $this->session->clear();
            }
        }

        public function verified(): bool
        {
            return $this->valid || $this->session->get('validUser') === true;
        }

        public function setPermission(string $list): self
        {
            if ($this->isjwt) {
                $this->app->response()->setHeaders('authorization', 'Bearer ' . $list);
            }
            $this->valid = true;
            $this->permissions = $list;
            $this->session->put([
                'validUser' => $this->valid,
                'permission' => $this->permissions
            ]);
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
