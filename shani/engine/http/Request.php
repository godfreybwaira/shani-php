<?php

/**
 * Description of Response
 * @author coder
 *
 * Created on: Feb 11, 2024 at 6:50:02 PM
 */

namespace shani\engine\http {

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

        public function languages(): array
        {
            $accept = $this->headers('accept-language');
            if ($accept !== null) {
                $langs = explode(',', $accept);
                return array_map(fn($val) => strtolower(trim(explode(';', $val)[0])), $langs);
            }
            return [];
        }

        public function module(): string
        {
            return $this->url['module'];
        }

        public function callback(): string
        {
            return $this->url['callback'];
        }

        public function resource(): string
        {
            return $this->url['resource'];
        }

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

        public function type(): ?string
        {
            if (!$this->type) {
                $this->type = \library\Mime::explode($this->headers('content-type'))[1] ?? null;
            }
            return $this->type;
        }

        public function isAsync(): bool
        {
            return $this->headers('x-request-mode') === 'async';
        }

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

        public function version(): ?string
        {
            if ($this->version === null) {
                $this->platform();
            }
            return $this->version;
        }

        public function ip(): string
        {
            return $this->req->ip();
        }

        public function time(): int
        {
            return $this->req->time();
        }

        public function params($index = null, bool $selected = true)
        {
            return \library\Map::get($this->url['params'], $index, $selected);
        }

        public function query($names = null, bool $selected = true)
        {
            if (empty($this->queryValues)) {
                $this->queryValues = \library\Map::normalize($this->req->get());
            }
            return \library\Map::get($this->queryValues, $names, $selected);
        }

        public function cookies($names = null, bool $selected = true)
        {
            return \library\Map::get($this->req->cookies(), $names, $selected);
        }

        public function read($names = null, bool $selected = true)
        {
            if (!empty($this->inputs)) {
                return \library\Map::get($this->inputs, $names, $selected);
            }
            return $this->req->raw();
        }

        public function headers($names = null, bool $selected = true)
        {
            return \library\Map::get($this->req->headers(), $names, $selected);
        }

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
                'module' => '/' . $params[0], 'callback' => '/' . ($params[4] ?? $resource)
            ];
        }
    }

}
