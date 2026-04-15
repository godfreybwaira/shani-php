<?php

/**
 * Description of RequestRoute
 * @author coder
 *
 * Created on: Mar 6, 2025 at 11:50:18 AM
 */

namespace shani\http {

    use shani\launcher\Framework;

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
        public readonly ?string $extension;

        public function __construct(string $module, string $controller, string $action, array $params = [], ?string $extension = null)
        {
            $this->params = $params;
            $this->module = $module;
            $this->action = $action;
            $this->extension = $extension;
            $this->controller = $controller;
        }

        /**
         * Create a new Request Rout object from path while the query string is ignored.
         * Normally the path can be request uri or any other similar structure.
         * @param string $path Request uri path e.g /users/0/profile
         */
        public static function fromPath(string $path): self
        {
            $cleanPath = strtolower(trim($path, '/'));
            $idx = strpos($cleanPath, '?');
            if ($idx !== false) {
                $cleanPath = substr($cleanPath, 0, $idx);
            }
            $url = explode('.', $cleanPath);
            $params = explode('/', $url[0]);
            $module = $params[0];
            $controller = $params[2] ?? $module;
            $action = $params[4] ?? Framework::HOME_FUNCTION;
            $extension = $url[1] ?? null;
            return new self($module, $controller, $action, $params, $extension);
        }
    }

}
