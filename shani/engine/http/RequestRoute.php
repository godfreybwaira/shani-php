<?php

/**
 * Description of RequestRoute
 * @author coder
 *
 * Created on: Mar 6, 2025 at 11:50:18 AM
 */

namespace shani\engine\http {

    use shani\engine\core\Definitions;

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

        public function __construct(string $path)
        {
            $idx = strpos($path, '?');
            if ($idx !== false) {
                $path = substr($path, 0, $idx);
            }
            $url = explode('.', strtolower(trim($path, '/')));
            $this->params = explode('/', $url[0]);
            $this->resource = '/' . $this->params[2] ?? $this->params[0];
            $this->module = '/' . $this->params[0];
            $this->callback = '/' . ($this->params[4] ?? Definitions::HOME_FUNCTION);
        }
    }

}
