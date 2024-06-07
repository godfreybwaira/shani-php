<?php

/**
 * Description of Authorization
 * @author coder
 *
 * Created on: Jun 4, 2024 at 11:24:22 AM
 */

namespace shani\engine\authorization {

    abstract class Authorization
    {

        public const AUTH_SESSION = 0, AUTH_JWT = 1;

        private \shani\engine\http\Session $session;

        protected function __construct(string $sessionName)
        {
            $this->session = new \shani\engine\http\Session($sessionName);
        }

        public function verified(): bool
        {
            return $this->session->get('validUser');
        }

        public function setPermission(string $list): self
        {
            $this->session->put(['validUser' => true, 'permission' => $list]);
            return $this;
        }

        public function hasPermission(string $url): bool
        {
            $list = $this->session->get('permission');
            if ($list === '*') {
                return true;
            }
            $code = \library\Utils::digest($url);
            return $list !== null && preg_match('/\b' . $code . '\b/', $list);
        }
    }

}
