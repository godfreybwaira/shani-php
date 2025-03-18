<?php

/**
 * Create and manage HTTP Cookie
 * @author coder
 *
 * Created on: Mar 27, 2024 at 1:44:06 PM
 */

namespace lib {

    use shani\contracts\HttpCookie;

    final class Cookie implements HttpCookie
    {

        public const SAME_SITE_NONE = 'None';
        public const SAME_SITE_LAX = 'Lax';
        public const SAME_SITE_STRICT = 'Strict';

        private array $cookie;

        public function __construct(string $rawCookie = null)
        {
            if ($rawCookie !== null) {
                $attributes = preg_split('/\s*;\s*/', $rawCookie, -1, PREG_SPLIT_NO_EMPTY);
                if (!$attributes) {
                    throw new \InvalidArgumentException('Could not parse raw cookie: ' . $rawCookie);
                }
                $this->parse($attributes);
            }
        }

        private function parse(array $attributes)
        {
            $nameValue = explode('=', array_shift($attributes), 2);
            $this->cookie = [
                'name' => $nameValue[0],
                'value' => isset($nameValue[1]) ? urldecode($nameValue[1]) : null
            ];
            while ($attr = array_shift($attributes)) {
                $pair = explode('=', $attr, 2);
                $name = strtolower($pair[0]);
                $value = $pair[1] ?? null;
                if (in_array($name, ['secure', 'httponly'], true)) {
                    $this->cookie[$name] = true;
                } else if ($name === 'max-age') {
                    $this->cookie['max-age'] = (int) $value;
                } else {
                    $this->cookie[$name] = $value;
                }
            }
        }

        #[\Override]
        public function name(): string
        {
            return $this->cookie['name'];
        }

        #[\Override]
        public function value(): string
        {
            return $this->cookie['value'];
        }

        /**
         * Returns an instance with the specified value.
         *
         * @param string $value
         * @return static
         */
        public function setValue(string $value): self
        {
            $this->cookie['value'] = $value;
            return $this;
        }

        /**
         * Gets the max-age attribute.
         *
         * @return \DateTime
         */
        public function maxAge(): \DateTime
        {
            return $this->cookie['max-age'];
        }

        /**
         * Gets the time the cookie expires.
         *
         * @return \DateTime|null
         */
        public function expires(): ?\DateTime
        {
            return $this->cookie['expires'] ?? null;
        }

        /**
         * Whether this cookie is expired.
         *
         * @return bool
         */
        public function isExpired(): bool
        {
            if (!empty($this->cookie['expires'])) {
                return time() - $this->cookie['expires']->getTimestamp() <= 0;
            }
            return true;
        }

        /**
         * Returns an instance with the specified expires.
         * @param \DateTimeInterface $expires Time to expire
         * @return static
         */
        public function setExpires(\DateTimeInterface $expires): self
        {
            $this->cookie['expires'] = $expires;
            return $this;
        }

        /**
         * Set Cookie maximum age
         * @param \DateTimeInterface $maxAge
         * @return self
         */
        public function setMaxAge(\DateTimeInterface $maxAge): self
        {
            $this->cookie['max-age'] = $maxAge;
            return $this;
        }

        /**
         * Gets the domain of the cookie.
         * @return string|null
         */
        public function domain(): ?string
        {
            return $this->cookie['domain'] ?? null;
        }

        /**
         * Set Cookie domain
         * @param string|null $domain
         * @return static
         */
        public function setDomain(string $domain): self
        {
            $this->cookie['domain'] = $domain;
            return $this;
        }

        /**
         * Gets the path of the cookie.
         * @return string
         */
        public function path(): ?string
        {
            return $this->cookie['path'] ?? null;
        }

        /**
         * Set cookie path
         * @param string $path
         * @return static
         */
        public function setPath(string $path): self
        {
            $this->cookie['path'] = $path;
            return $this;
        }

        /**
         * Set cookie name
         * @param string $name a cookie name
         * @return self
         */
        public function setName(string $name): self
        {
            $this->cookie['name'] = $name;
            return $this;
        }

        /**
         * Whether the cookie should only be transmitted over a secure HTTPS connection.
         * @return bool
         */
        public function isSecure(): bool
        {
            return $this->cookie['secure'] ?? false;
        }

        /**
         * Returns an instance with the specified enabling or
         * disabling cookie transmission over a secure HTTPS connection.
         * @param bool $secure
         * @return static
         */
        public function setSecure(bool $secure = true): self
        {
            $this->cookie['secure'] = $secure;
            return $this;
        }

        /**
         * Whether the cookie can be accessed only through the HTTP protocol.
         * @return bool
         */
        public function isHttpOnly(): bool
        {
            return $this->cookie['httponly'] ?? false;
        }

        /**
         * Returns an instance with the specified enable or
         * disable cookie transmission over the HTTP protocol only.
         * @param bool $httpOnly
         * @return static
         */
        public function setHttpOnly(bool $httpOnly = true): self
        {
            $this->cookie['httponly'] = $httpOnly;
            return $this;
        }

        /**
         * Gets the SameSite attribute.
         *
         * @return string|null
         */
        public function sameSite(): ?string
        {
            return $this->cookie['samesite'] ?? null;
        }

        /**
         * Returns an instance with the specified SameSite attribute.
         *
         * @param string|null $sameSite
         * @return static
         */
        public function setSameSite(?string $sameSite): self
        {
            $this->cookie['samesite'] = $sameSite;
            return $this;
        }

        /**
         * Returns the cookie as a string representation.
         *
         * @return string
         */
        public function __toString(): string
        {
            $cookie = $this->cookie['name'] . '=' . $this->cookie['value'];
            if (!empty($this->cookie['expires'])) {
                $cookie .= '; Expires=' . gmdate(DATE_COOKIE, $this->cookie['expires']->getTimestamp());
            }
            if (array_key_exists('max-age', $this->cookie)) {
                $cookie .= '; Max-Age=' . $this->cookie['max-age']->getTimestamp() - time();
            }
            if (!empty($this->cookie['path'])) {
                $cookie .= '; Path=' . $this->cookie['path'];
            }
            if (!empty($this->cookie['samesite'])) {
                $cookie .= '; SameSite=' . $this->cookie['samesite'];
            }
            if (!empty($this->cookie['domain'])) {
                $cookie .= '; Domain=' . $this->cookie['domain'];
            }
            if (!empty($this->cookie['secure'])) {
                $cookie .= '; Secure';
            }
            if (!empty($this->cookie['httponly'])) {
                $cookie .= '; HttpOnly';
            }
            return $cookie;
        }
    }

}
