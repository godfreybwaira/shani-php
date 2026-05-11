<?php

/**
 * Description of RequestRoute
 * @author coder
 *
 * Created on: Mar 6, 2025 at 11:50:18 AM
 */

namespace shani\http {

    use shani\launcher\Framework;
    use shani\launcher\ShaniUtils;

    final class RequestRoute
    {

        /**
         * Request parameters
         * @var array
         */
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

        public function __construct(string $module, ?string $action = null, array $params = [], ?string $extension = null)
        {
            $this->params = $params;
            $this->module = $module;
            $this->extension = $extension;
            $this->action = ShaniUtils::kebab2camelCase($action ?? Framework::HOME_FUNCTION);
            $this->controller = ShaniUtils::kebab2PascalCase($module . '-' . Framework::SUFFIX_CONTROLLER);
        }

        /**
         * Create a new Request Rout object from path while the query string is ignored.
         * Normally the path can be request uri or any other similar structure.
         * @param string $path Request uri path e.g /users/1/profile/2
         */
        public static function fromPath(string $path): self
        {
            $cleanPath = strtolower(trim($path, '/'));
            $idx = strpos($cleanPath, '?');
            if ($idx !== false) {
                $cleanPath = substr($cleanPath, 0, $idx);
            }
            $parts = explode('.', $cleanPath);
            $params = explode('/', $parts[0]);
            $action = $params[2] ?? null;
            $extension = $parts[1] ?? null;
            return new self($params[0], $action, $params, $extension);
        }
    }

}
