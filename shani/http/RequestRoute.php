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
         * Current application resource name
         * @var string
         */
        public readonly string $resource;

        /**
         * Current application module name
         * @var string
         */
        public readonly string $module;

        /**
         * Current application callback name
         * @var string
         */
        public readonly string $callback;

        /**
         * The current request target referring to a path to a class function
         * (i.e method/module/resource/callback)
         * @var string
         */
        public readonly string $target;

        /**
         * Create a new Request Rout object
         * @param string $method Request method in lowercase e.g get, post etc
         * @param string $path Request uri path e.g /users/0/profile
         */
        public function __construct(string $method, string $path)
        {
            $cleanPath = strtolower(trim($path, '/'));
            $idx = strpos($cleanPath, '?');
            if ($idx !== false) {
                $cleanPath = substr($cleanPath, 0, $idx);
            }
            $url = explode('.', $cleanPath);
            $this->params = explode('/', $url[0]);
            $this->resource = '/' . ($this->params[2] ?? $this->params[0]);
            $this->module = '/' . $this->params[0];
            $this->callback = '/' . ($this->params[4] ?? Definitions::HOME_FUNCTION);
            $this->target = $method . $this->module . $this->resource . $this->callback;
        }
    }

}
