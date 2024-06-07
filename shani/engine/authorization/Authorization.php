<?php

/**
 * Description of Authorization
 * @author coder
 *
 * Created on: Jun 4, 2024 at 11:24:22 AM
 */

namespace shani\engine\authorization {

    use shani\engine\config\AppConfig;
    use shani\engine\http\Session;

    final class Authorization
    {

        public const AUTH_SESSION = 0, AUTH_JWT = 1;
        private const NAME = '_mGnUs$nrWM0';

        private Session $session;
        private AppConfig $config;

        public function __construct(AppConfig &$config)
        {
            $this->config = $config;
            $this->session = new Session(self::NAME);
        }

        public function verified(): bool
        {
            return $this->session->get('validUser') === true;
        }

        public function setPermission(string $list): self
        {
            $this->session->put(['validUser' => true, 'permission' => $list]);
            return $this;
        }

        public function hasPermission(string $url): bool
        {
            $list = $this->session->get('permission');
            if ($list === null) {
                return false;
            }
            return $list === '*' || preg_match('/\b' . \library\Utils::digest($url) . '\b/', $list);
        }
    }

}
