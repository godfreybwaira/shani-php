<?php

/**
 * Description of URI
 * @author coder
 *
 * Created on: Mar 22, 2024 at 9:06:19 AM
 */

namespace library {

    final class URI
    {

        private string $uri, $path;
        private array $parts;

        public function __construct(string $uri)
        {
            $this->uri = rawurldecode($uri);
            $this->parts = parse_url($this->uri);
            if (!is_array($this->parts)) {
                throw new \InvalidArgumentException('Invalid URI detected.');
            }
            $this->path = '/' . trim(str_replace([chr(0), '/..', '/.'], '', $this->parts['path']), '/');
        }

        private static function valueOf($value, $default = null)
        {
            return !empty($value) ? $value : $default;
        }

        private function copy($key, $value): self
        {
            if ($this->parts[$key] === $value) {
                return $this;
            }
            $copy = clone $this;
            $copy->parts[$key] = $value;
            return $copy;
        }

        public function __toString(): string
        {
            return $this->uri;
        }

        public function authority(): string
        {
            $info = self::valueOf($this->userInfo());
            $port = self::valueOf($this->parts['port']);
            return ($info ? $info . '@' : null ) . $this->hostname() . ($port ? ':' . $port : null);
        }

        /**
         * Get URI fragment
         * @return string|null URI fragment
         */
        public function fragment(): ?string
        {
            return self::valueOf($this->parts['fragment']);
        }

        /**
         * Get hostname from URI
         * @return string Hostname
         */
        public function hostname(): string
        {
            return $this->parts['host'];
        }

        public function subdomain(): string
        {
            return substr($this->parts['host'], 0, strpos($this->parts['host'], '.'));
        }

        public function tld(): string
        {
            return substr($this->parts['host'], strrpos($this->parts['host'], '.') + 1);
        }

        public function host(): string
        {
            $scheme = $this->scheme();
            $port = self::valueOf($this->parts['port']);
            $host = $this->parts['host'] . ($port ? ':' . $port : null);
            return $scheme !== null ? $scheme . '://' . $host : $host;
        }

        /**
         * Get the sanitized URI path.
         * @return string URI path
         */
        public function path(): string
        {
            return $this->path;
        }

        /**
         * Get the URI path joined with query string.
         * @return string URI path joined with query string.
         */
        public function pathInfo(): string
        {
            $path = $this->path;
            $query = $this->query();
            $fragment = $this->fragment();
            if ($query !== null) {
                $path .= '?' . $query;
            }
            if ($fragment !== null) {
                $path .= '#' . $fragment;
            }
            return $path;
        }

        public function port(): ?int
        {
            return $this->parts['port'];
        }

        /**
         * Get query string part of a URI
         * @return string|null Query string
         */
        public function query(): ?string
        {
            return $this->parts['query'] ?? null;
        }

        public function scheme(): ?string
        {
            return self::valueOf($this->parts['scheme']);
        }

        public function userInfo(): ?string
        {
            $pass = !empty($this->parts['pass']) ? ':' . $this->parts['pass'] : null;
            return self::valueOf($this->parts['user'] . $pass);
        }

        public function withFragment(string $fragment): self
        {
            return $this->copy('fragment', $fragment);
        }

        public function withHost(string $host): self
        {
            return $this->copy('host', $host);
        }

        public function withPath(string $path): self
        {
            return $this->copy('path', $path);
        }

        public function withPort(?int $port): self
        {
            return $this->copy('port', $port);
        }

        public function withLocation(string $location): self
        {
            $parts = explode('?', $location);
            if (!empty($parts[1])) {
                return $this->withPath($parts[0])->withQuery($parts[1]);
            }
            return $this->withPath($parts[0]);
        }

        public function withQuery(string $query): self
        {
            return $this->copy('query', $query);
        }

        public function withScheme(string $scheme): self
        {
            return $this->copy('scheme', $scheme);
        }

        public function withUserInfo(string $user, ?string $password = null): self
        {
            return $this->copy('user', $user)->copy('pass', $password);
        }

        /**
         * Check if request URI was made through secure connection
         * @param type $scheme URI scheme
         * @return bool True on success, false otherwise
         */
        public function secure($scheme = 'https'): bool
        {
            return $this->scheme() === $scheme;
        }
    }

}
