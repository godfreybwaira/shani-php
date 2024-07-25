<?php

/**
 * Description of Response
 * @author coder
 *
 * Created on: Feb 11, 2024 at 6:50:02 PM
 */

namespace shani\engine\http {

    use shani\engine\core\Definitions;

    final class Request
    {

        private ?string $type = null;
        private \shani\contracts\Request $req;
        private ?array $url, $inputs, $queryValues = null;
        private ?string $platform = null, $version = null, $accepted = null;

        public function __construct(\shani\contracts\Request &$req)
        {
            $this->url = self::explodePath($req->uri()->path());
            $files = $req->files();
            $post = $req->post();
            $this->req = $req;
            if (empty($post)) {
                $this->inputs = $this->parseRawData();
                if ($files !== null) {
                    $this->inputs = !empty($this->inputs) ? array_merge($this->inputs, $files) : $files;
                }
            } else {
                $post = \library\Map::normalize($post);
                $this->inputs = !empty($files) ? array_merge($post, $files) : $post;
            }
        }

        private function parseRawData(): ?array
        {
            $type = $this->type();
            if ($type === 'json') {
                return json_decode($this->req->raw(), true);
            } if ($type === 'xml') {
                return \library\DataConvertor::xml2array($this->req->raw());
            } if ($type === 'x-www-form-urlencoded') {
                $rawData = null;
                parse_str($this->req->raw(), $rawData);
                return $rawData;
            } if ($type === 'yaml') {
                return \library\DataConvertor::yaml2array($this->req->raw());
            } if ($type === 'csv') {
                return str_getcsv($this->req->raw());
            } return null;
        }

        /**
         * Get all the columns that a user application wish to get values from.
         * This function enable the application to fetch only needed data, no more, no less
         * @param array $availableColumns Columns to choose from
         * @param string $lookupHeader HTTP header contains the list of columns.
         * separated by comma, default header being x-request-columns
         * @return array Columns that user application wish to get values from
         */
        public function columns(array $availableColumns, string $lookupHeader = 'x-request-columns'): array
        {
            $headerString = $this->headers($lookupHeader);
            if (empty($headerString)) {
                return $availableColumns;
            }
            $collections = [];
            foreach ($availableColumns as $key => $value) {
                $col = is_int($key) ? $value : $key;
                if (preg_match('/\b' . $col . '\b/i', $headerString) === 1) {
                    $collections[$key] = $value;
                }
            }
            return $collections;
        }

        /**
         * Get use request language codes. These values will be used for application
         * language selection if the values are supported.
         * @return array users accepted languages
         */
        public function languages(): array
        {
            $accept = $this->headers('accept-language');
            if ($accept !== null) {
                $langs = explode(',', $accept);
                return array_map(fn($val) => strtolower(trim(explode(';', $val)[0])), $langs);
            }
            return [];
        }

        /**
         * Get current application module name
         * @return string Module name
         */
        public function module(): string
        {
            return $this->url['module'];
        }

        /**
         * Get current application callback name
         * @return string Callback name
         */
        public function callback(): string
        {
            return $this->url['callback'];
        }

        /**
         * Get current application resource name
         * @return string Resource name
         */
        public function resource(): string
        {
            return $this->url['resource'];
        }

        /**
         * Check if HTTP user agent accept the given type.
         * @param string $type MIME type or last part of MIME before /
         * @return bool True on success, false otherwise.
         */
        public function accept(string $type): bool
        {
            if ($this->accepted === null) {
                $this->accepted = \library\Mime::parse($this->headers('accept'));
            }
            if ($this->accepted !== null) {
                if (strpos($type, '/') !== false) {
                    return in_array($type, $this->accepted);
                }
                foreach ($this->accepted as $mime) {
                    $idx = strpos($mime, '/') + 1;
                    if (substr($mime, $idx) === $type) {
                        return true;
                    }
                }
            }
            return false;
        }

        /**
         * Get request MIME type
         * @return string|null content type
         */
        public function type(): ?string
        {
            if (!$this->type) {
                $this->type = \library\Mime::explode($this->headers('content-type'))[1] ?? null;
            }
            return $this->type;
        }

        /**
         * Check if HTTP request is requested via asynchronous mode. This value can
         * be set by HTTP x-request-mode request header. It is useful for example
         * if the request is made via AJAX or any other same technologies
         * @return bool True if the request is asynchronous, false otherwise
         */
        public function isAsync(): bool
        {
            return $this->headers('x-request-mode') === 'async';
        }

        /**
         * Get HTTP preferred request context (platform) set by user agent. This
         * value is set via HTTP accept-version header and the accepted values are
         * 'web' and 'api' only. User can also set preferred application version
         * after request context, separated by semicolon
         * @example accept-version=web;1.0
         * @return string|null
         */
        public function platform(): ?string
        {
            if ($this->platform === null) {
                $str = $this->headers('accept-version');
                if ($str === null) {
                    $this->platform = 'web';
                } else {
                    $list = explode(';', strtolower($str));
                    $this->version = !empty($list[1]) ? trim($list[1]) : null;
                    $this->platform = $list[0];
                }
            }
            return $this->platform;
        }

        /**
         * Get user HTTP requested application version.
         * @return string|null
         */
        public function version(): ?string
        {
            if ($this->version === null) {
                $this->platform();
            }
            return $this->version;
        }

        /**
         * Get user IP address
         * @return string User IP address
         */
        public function ip(): string
        {
            return $this->req->ip();
        }

        /**
         * Get user request time
         * @return int
         */
        public function time(): int
        {
            return $this->req->time();
        }

        /**
         * Get request parameters sent via HTTP request endpoint
         * @param type $index Index of request parameter
         * @param bool $selected If set to true, only the selected values will be returned.
         * @return type
         */
        public function params($index = null, bool $selected = true)
        {
            return \library\Map::get($this->url['params'], $index, $selected);
        }

        /**
         * Get HTTP queries
         * @param type $names query string name, can be array or string
         * @param bool $selected If set to true, only the selected values will be returned.
         * @return type
         */
        public function query($names = null, bool $selected = true)
        {
            if (empty($this->queryValues)) {
                $this->queryValues = \library\Map::normalize($this->req->get());
            }
            return \library\Map::get($this->queryValues, $names, $selected);
        }

        /**
         * Get HTTP cookie value(s)
         * @param type $names named key, can be string or array
         * @param bool $selected If set to true, only the selected values will be returned.
         * @return type
         */
        public function cookies($names = null, bool $selected = true)
        {
            return \library\Map::get($this->req->cookies(), $names, $selected);
        }

        /**
         * Get HTTP request values obtained via HTTP request body.
         * @param type $names named key, can be string or array
         * @param bool $selected If set to true, only the selected values will be returned.
         * @return type
         */
        public function body($names = null, bool $selected = true)
        {
            if (!empty($this->inputs)) {
                return \library\Map::get($this->inputs, $names, $selected);
            }
            return $this->req->raw();
        }

        /**
         * Get HTTP request headers
         * @param type $names header name, can be string or array
         * @param bool $selected If set to true, only the selected values will be returned.
         * @return type
         */
        public function headers($names = null, bool $selected = true)
        {
            return \library\Map::get($this->req->headers(), $names, $selected);
        }

        /**
         * Get HTTP request method
         * @return string HTTP request method
         */
        public function method(): string
        {
            return $this->req->method();
        }

        public function uri(): \library\URI
        {
            return $this->req->uri();
        }

        public function localhost(): bool
        {
            return $this->req->ip() === '127.0.0.1';
        }

        /**
         * Check if HTTP request was made via secure connection
         * @return bool True on success, false otherwise
         */
        public function secure(): bool
        {
            return $this->req->uri()->scheme() === 'https';
        }

        public function path(string $name = null): ?string
        {
            $parts = $name === null ? $this->url : self::explodePath($name);
            if (!empty($parts)) {
                return $this->req->method() . $parts['module'] . $parts['resource'] . $parts['callback'];
            }
            return null;
        }

        public function forward(string $path): self
        {
            $this->url = self::explodePath($path);
            return $this;
        }

        private static function explodePath(string $path): array
        {
            $idx = strpos($path, '?');
            if ($idx !== false) {
                $path = substr($path, 0, $idx);
            }
            $url = explode('.', strtolower(trim($path, '/')));
            $params = explode('/', $url[0]);
            $resource = $params[2] ?? $params[0];
            return [
                'params' => $params, 'resource' => '/' . $resource,
                'module' => '/' . $params[0], 'callback' => '/' . ($params[4] ?? Definitions::HOME_FUNCTION)
            ];
        }
    }

}
