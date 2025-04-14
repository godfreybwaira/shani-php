<?php

/**
 * Description of URI
 * @author coder
 *
 * Created on: Mar 22, 2024 at 9:06:19 AM
 */

namespace lib {

    final class URI
    {

        /**
         * The sanitized URI path
         * @var string
         */
        public readonly string $path;

        /**
         * host name from URI
         * @var string
         */
        public readonly string $hostname;

        /**
         * Host port
         * @var string
         */
        public readonly string $port;

        /**
         * URI fragment
         * @var string
         */
        public readonly ?string $fragment;

        /**
         * URI query string
         * @var string
         */
        public readonly ?string $query;

        /**
         * URI scheme
         * @var string|null
         */
        public readonly ?string $scheme;
        private readonly string $uri;
        private array $parts;

        public function __construct(string $uri)
        {
            $this->uri = rawurldecode($uri);
            $this->parts = parse_url($this->uri);
            if (!is_array($this->parts)) {
                throw new \InvalidArgumentException('Invalid URI detected.');
            }
            $this->path = '/' . trim(str_replace([chr(0), '/..', '/.'], '', $this->parts['path']), '/');
            $this->hostname = $this->parts['host'];
            $this->port = $this->parts['port'] ?? null;
            $this->fragment = $this->parts['fragment'] ?? null;
            $this->query = $this->parts['query'] ?? null;
            $this->scheme = $this->parts['scheme'] ?? null;
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
            return ($info ? $info . '@' : null) . $this->hostname . ($this->port ? ':' . $this->port : null);
        }

        public function subdomain(): string
        {
            return substr($this->hostname, 0, strpos($this->hostname, '.'));
        }

        public function tld(): string
        {
            return substr($this->hostname, strrpos($this->hostname, '.') + 1);
        }

        /**
         * Get full host name including scheme, hostname and port (i.e scheme://host:00)
         * @return string
         */
        public function host(): string
        {
            $host = $this->hostname . ($this->port ? ':' . $this->port : null);
            return $this->scheme !== null ? $this->scheme . '://' . $host : $host;
        }

        /**
         * Get the URI path joined with query string.
         * @return string URI path joined with query string.
         */
        public function pathInfo(): string
        {
            $path = $this->path;
            if ($this->query !== null) {
                $path .= '?' . $this->query;
            }
            if ($this->fragment !== null) {
                $path .= '#' . $this->fragment;
            }
            return $path;
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

        public function withPort(int $port): self
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
         * @return bool True on success, false otherwise
         */
        public function secure(): bool
        {
            return $this->scheme === 'https' || $this->scheme === 'wss';
        }
    }

}
