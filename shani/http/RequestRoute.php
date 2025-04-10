<?php

/**
 * Description of RequestRoute
 * @author coder
 *
 * Created on: Mar 6, 2025 at 11:50:18 AM
 */

namespace shani\http {

    use shani\core\Definitions;

    final class RequestRoute
    {

        public readonly array $params;

        /**
         * Current application controller name
         * @var string
         */
        public readonly string $controller;

        /**
         * Current application module name
         * @var string
         */
        public readonly string $module;

        /**
         * Current application callback name
         * @var string
         */
        public readonly string $action;

        /**
         * Create a new Request Rout object
         * @param string $path Request uri path e.g /users/0/profile
         */
        public function __construct(string $path)
        {
            $cleanPath = strtolower(trim($path, '/'));
            $idx = strpos($cleanPath, '?');
            if ($idx !== false) {
                $cleanPath = substr($cleanPath, 0, $idx);
            }
            $url = explode('.', $cleanPath);
            $this->params = explode('/', $url[0]);
            $this->controller = '/' . ($this->params[2] ?? $this->params[0]);
            $this->module = '/' . $this->params[0];
            $this->action = '/' . ($this->params[4] ?? Definitions::HOME_FUNCTION);
        }
    }

}
