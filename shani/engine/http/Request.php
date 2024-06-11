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
        private ?array $url, $inputs = null;
        private \shani\contracts\Request $req;
        private ?string $platform = null, $version = null, $accepted = null;

        public function __construct(\shani\contracts\Request &$req)
        {
            $this->url = self::explodePath($req->uri()->path());
            $files = $req->files();
            $post = $req->post();
            $this->req = $req;
            if (empty($post)) {
                $this->parseRawData();
                if ($files !== null) {
                    $this->inputs = $this->inputs ? array_merge($this->inputs, $files) : $files;
                }
            } else {
                $this->inputs = $files ? array_merge($post, $files) : $post;
            }
        }

        private function parseRawData(): void
        {
            $type = $this->type();
            if ($type === null) {
                return;
            }
            if ($type === 'json') {
                $this->inputs = json_decode($this->req->raw(), true);
            } elseif ($type === 'xml') {
                $this->inputs = \library\DataConvertor::xml2array($this->req->raw());
            } elseif ($type === 'x-www-form-urlencoded') {
                parse_str($this->req->raw(), $this->inputs);
            } elseif ($type === 'yaml') {
                $this->inputs = \library\DataConvertor::yaml2array($this->req->raw());
            } elseif ($type === 'csv') {
                $this->inputs = str_getcsv($this->req->raw());
            }
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
            return \library\Map::get($this->req->get(), $names, $selected);
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
