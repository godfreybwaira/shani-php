<?php

/**
 * Description of Response
 * @author coder
 *
 * Created on: Feb 11, 2024 at 6:50:02 PM
 */

namespace shani\engine\http {

    use shani\contracts\ServerRequest;
    use shani\engine\core\Definitions;

    final class Request
    {

        private ServerRequest $req;
        private ?string $type = null;
        private ?array $url, $inputs, $queryValues = null, $files = null;
        private ?string $platform = null, $version = null, $accepted = null;

        public function __construct(ServerRequest &$req)
        {
            $this->req = $req;
            $this->url = self::explodePath($req->uri()->path());
            $this->inputs = \library\Map::normalize($req->post());
            if (empty($this->inputs)) {
                $this->inputs = $this->parseRawData();
            }
        }

        /**
         * Get uploaded file by name and optional file index
         * @param string $name Name value as given in upload form
         * @param int $index File index in array, default is zero
         * @return UploadedFile|null Return uploaded file object if file is valid
         * and exists, false otherwise.
         */
        public function file(string $name, int $index = 0): ?UploadedFile
        {
            if (!empty($this->files[$name][$index])) {
                return $this->files[$name][$index];
            }
            $files = $this->req->files();
            if (!empty($files[$name][$index])) {
                $this->files[$name][$index] = new UploadedFile($files[$name][$index]);
                return $this->files[$name][$index];
            }
            return null;
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
                if (str_contains($type, '/')) {
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
         * Check if HTTP request is requested via asynchronous mode using x-request-mode
         * request header value set to 'async'. For example if the request is made
         * via AJAX or any other same technologies
         * @return bool True if the request is asynchronous, false otherwise
         */
        public function isAsync(): bool
        {
            return $this->headers('x-request-mode') === 'async';
        }

        /**
         * Get HTTP preferred request context (platform) set by user agent. This
         * value is set via HTTP accept-version header and the accepted values are
         * 'web' and 'api'. User can also set application version after request context,
         * separated by semicolon. If none given, the 'web' context is assumed.
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
         * @param int|array $index Index of request parameter
         * @param bool $selected If set to true, only the selected values will be returned.
         * @return type
         */
        public function params(int|array $index = null, bool $selected = true)
        {
            return \library\Map::get($this->url['params'], $index, $selected);
        }

        /**
         * Get HTTP queries
         * @param string|array $names query string name
         * @param bool $selected If set to true, only the selected values will be returned.
         * @return type
         */
        public function query(string|array $names = null, bool $selected = true)
        {
            if (empty($this->queryValues)) {
                $this->queryValues = \library\Map::normalize($this->req->get());
            }
            return \library\Map::get($this->queryValues, $names, $selected);
        }

        /**
         * Get HTTP cookie value(s)
         * @param string|array $names named key
         * @param bool $selected If set to true, only the selected values will be returned.
         * @return type
         */
        public function cookies(string|array $names = null, bool $selected = true)
        {
            return \library\Map::get($this->req->cookies(), $names, $selected);
        }

        /**
         * Get HTTP request values obtained via HTTP request body.
         * @param string|array $names named key
         * @param bool $selected If set to true, only the selected values will be returned.
         * @return type
         */
        public function body(string|array $names = null, bool $selected = true)
        {
            if (!empty($this->inputs)) {
                return \library\Map::get($this->inputs, $names, $selected);
            }
            return $this->req->raw();
        }

        /**
         * Get HTTP request headers
         * @param string|array $names header name
         * @param bool $selected If set to true, only the selected values will be returned.
         * @return type
         */
        public function headers(string|array $names = null, bool $selected = true)
        {
            return \library\Map::get($this->req->headers(), $names, $selected);
        }

        /**
         * Get HTTP request method
         * @return string HTTP request method (in lowercase)
         */
        public function method(): string
        {
            return $this->req->method();
        }

        /**
         * Get the original unchanged request URI object
         * @return \library\URI Request URI object
         * @see self::path()
         */
        public function uri(): \library\URI
        {
            return $this->req->uri();
        }

        /**
         * Check if the request is from local machine
         * @return bool True on success, false otherwise
         */
        public function localhost(): bool
        {
            return $this->req->ip() === '127.0.0.1';
        }

        /**
         * Get the current request target referring to current path to a class function
         * (i.e method/module/resource/callback)
         * @return string
         * @see App::hasAuthority()
         */
        public function target(): string
        {
            return $this->req->method() . $this->url['module'] . $this->url['resource'] . $this->url['callback'];
        }

        /**
         * Change the current request URL to a new value
         * @param string|Stringable $newUrl New URL or URI object
         * @return self
         */
        public function rewriteUrl(string|\Stringable $newUrl): self
        {
            $this->url = self::explodePath($newUrl);
            return $this;
        }

        private static function explodePath(string|\Stringable $path): array
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
