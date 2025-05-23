<?php

/**
 * Description of URI
 * @author coder
 *
 * Created on: Mar 22, 2024 at 9:06:19 AM
 */

namespace lib {

    final class URI implements \Stringable
    {

        private readonly string $uri;
        private array $parts;

        public function __construct(string $uri)
        {
            $this->uri = rawurldecode($uri);
            $this->parts = parse_url($this->uri);
            if (!is_array($this->parts)) {
                throw new \InvalidArgumentException('Invalid URI detected.');
            }
            if (!empty($this->parts['path'])) {
                $this->parts['path'] = '/' . trim(str_replace([chr(0), '/..', '/.'], '', $this->parts['path']), '/');
            }
        }

        private static function valueOf($value, $default = null)
        {
            return !empty($value) ? $value : $default;
        }

        private function copy(string $key, string|int|null $value): self
        {
            if ($this->parts[$key] === $value) {
                return $this;
            }
            $copy = clone $this;
            $copy->parts[$key] = $value;
            return $copy;
        }

        #[\Override]
        public function __toString(): string
        {
//            URI = scheme ":" ["//" authority] path ["?" query] ["#" fragment]
            $query = $this->query();
            $fragment = $this->fragment();
            $uri = $this->scheme() . '://' . $this->authority() . $this->path();
            $uri .= ($query !== null ? '?' . $query : null) . ($fragment !== null ? '#' . $fragment : null);
            return $uri;
        }

        public function authority(): string
        {
            $port = $this->port();
            $info = self::valueOf($this->userInfo());
            return ($info ? $info . '@' : null) . $this->parts['host'] . ($port ? ':' . $port : null);
        }

        public function subdomain(): string
        {
            return substr($this->parts['host'], 0, strpos($this->parts['host'], '.'));
        }

        public function tld(): string
        {
            return substr($this->parts['host'], strrpos($this->parts['host'], '.') + 1);
        }

        /**
         * Get URI scheme
         * @return string|null
         */
        public function scheme(): ?string
        {
            return $this->parts['scheme'] ?? null;
        }

        /**
         * Get URI query string
         * @return string|null
         */
        public function query(): ?string
        {
            return $this->parts['query'] ?? null;
        }

        /**
         * Get sanitized URL path
         * @return string
         */
        public function path(): ?string
        {
            return $this->parts['path'];
        }

        /**
         * Get host name without URI scheme and port number
         * @return string
         */
        public function hostname(): string
        {
            return $this->parts['host'];
        }

        /**
         * Get URI port number
         * @return string|null
         */
        public function port(): ?string
        {
            return $this->parts['port'] ?? null;
        }

        /**
         * Get full host name including scheme, hostname and port (i.e scheme://host:00)
         * @return string
         */
        public function host(): string
        {
            $port = $this->port();
            $host = $this->parts['host'] . ($port !== null ? ':' . $port : null);
            return $this->parts['scheme'] !== null ? $this->parts['scheme'] . '://' . $host : $host;
        }

        /**
         * Get the URI path joined with query string.
         * @return string URI path joined with query string.
         */
        public function pathInfo(): string
        {
            $path = $this->path();
            if ($this->query() !== null) {
                $path .= '?' . $this->query();
            }
            $fragment = $this->fragment();
            if ($fragment !== null) {
                $path .= '#' . $fragment;
            }
            return $path;
        }

        /**
         * Get URI fragment
         * @return string|null
         */
        public function fragment(): ?string
        {
            return $this->parts['fragment'] ?? null;
        }

        public function userInfo(): ?string
        {
            $pass = !empty($this->parts['pass']) ? ':' . $this->parts['pass'] : null;
            if (!empty($this->parts['user'])) {
                return $this->parts['user'] . $pass;
            }
            return $pass;
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
            return $this->scheme() === 'https' || $this->scheme() === 'wss';
        }
    }

}
