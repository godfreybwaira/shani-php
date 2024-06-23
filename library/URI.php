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
            $this->path = '/' . trim($this->parts['path'], '/');
        }

        private static function value($value, $default = null)
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
            $info = self::value($this->userInfo());
            $port = self::value($this->parts['port']);
            return ($info ? $info . '@' : null ) . $this->hostname() . ($port ? ':' . $port : null);
        }

        public function fragment(): ?string
        {
            return self::value($this->parts['fragment']);
        }

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
            $port = self::value($this->parts['port']);
            if ($scheme !== null) {
                return $scheme . '://' . $this->parts['host'] . ($port ? ':' . $port : null);
            }
            return $this->hostname() . ($port ? ':' . $port : null);
        }

        public function path(): string
        {
            return $this->path;
        }

        public function location(): string
        {
            $query = $this->query();
            return $this->path . ($query ? '?' . $query : null);
        }

        public function port(): ?int
        {
            return $this->parts['port'];
        }

        public function query(): ?string
        {
            return $this->parts['query'] ?? null;
        }

        public function scheme(): ?string
        {
            return self::value($this->parts['scheme']);
        }

        public function userInfo(): ?string
        {
            $pass = !empty($this->parts['pass']) ? ':' . $this->parts['pass'] : null;
            return self::value($this->parts['user'] . $pass);
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
    }

}
