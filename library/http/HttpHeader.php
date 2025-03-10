<?php

namespace library\http {


    final class HttpHeader
    {

        public const ACCEPT = 'Accept';
        public const ACCEPT_CHARSET = 'Accept-Charset';
        public const ACCEPT_ENCODING = 'Accept-Encoding';
        public const ACCEPT_LANGUAGE = 'Accept-Language';
        public const ACCEPT_PATCH = 'Accept-Patch';
        public const ACCEPT_RANGES = 'Accept-Ranges';
        public const ACCEPT_VERSION = 'Accept-Version';
        public const ACCESS_CONTROL_ALLOW_CREDENTIALS = 'Access-Control-Allow-Credentials';
        public const ACCESS_CONTROL_ALLOW_HEADERS = 'Access-Control-Allow-Headers';
        public const ACCESS_CONTROL_ALLOW_METHODS = 'Access-Control-Allow-Methods';
        public const ACCESS_CONTROL_ALLOW_ORIGIN = 'Access-Control-Allow-Origin';
        public const ACCESS_CONTROL_EXPOSE_HEADERS = 'Access-Control-Expose-Headers';
        public const ACCESS_CONTROL_MAX_AGE = 'Access-Control-Max-Age';
        public const ACCESS_CONTROL_REQUEST_HEADERS = 'Access-Control-Request-Headers';
        public const ACCESS_CONTROL_REQUEST_METHOD = 'Access-Control-Request-Method';
        public const AGE = 'Age';
        public const ALLOW = 'Allow';
        public const AUTHORIZATION = 'Authorization';
        public const CACHE_CONTROL = 'Cache-Control';
        public const CONNECTION = 'Connection';
        public const CONTENT_ENCODING = 'Content-Encoding';
        public const CONTENT_DISPOSITION = 'Content-Disposition';
        public const CONTENT_LANGUAGE = 'Content-Language';
        public const CONTENT_LENGTH = 'Content-Length';
        public const CONTENT_LOCATION = 'Content-Location';
        public const CONTENT_RANGE = 'Content-Range';
        public const CONTENT_TYPE = 'Content-Type';
        public const CONTENT_SECURITY_POLICY = 'Content-Security-Policy';
        public const COOKIE = 'Cookie';
        public const CROSS_ORIGIN_RESOURCE_POLICY = 'Cross-Origin-Resource-Policy';
        public const DATE = 'Date';
        public const ETAG = 'ETag';
        public const EXPECT = 'Expect';
        public const EXPIRES = 'Expires';
        public const FROM = 'From';
        public const HOST = 'Host';
        public const IF_MATCH = 'If-Match';
        public const IF_MODIFIED_SINCE = 'If-Modified-Since';
        public const IF_NONE_MATCH = 'If-None-Match';
        public const IF_RANGE = 'If-Range';
        public const IF_UNMODIFIED_SINCE = 'If-Unmodified-Since';
        public const LAST_MODIFIED = 'Last-Modified';
        public const LINK = 'Link';
        public const LOCATION = 'Location';
        public const MAX_FORWARDS = 'Max-Forwards';
        public const ORIGIN = 'Origin';
        public const PRAGMA = 'Pragma';
        public const PROXY_AUTHENTICATE = 'Proxy-Authenticate';
        public const PROXY_AUTHORIZATION = 'Proxy-Authorization';
        public const RANGE = 'Range';
        public const REFERER = 'Referer';
        public const RETRY_AFTER = 'Retry-After';
        public const SERVER = 'Server';
        public const SET_COOKIE = 'Set-Cookie';
        public const SET_COOKIE2 = 'Set-Cookie2';
        public const TE = 'TE';
        public const TRAILER = 'Trailer';
        public const TRANSFER_ENCODING = 'Transfer-Encoding';
        public const UPGRADE = 'Upgrade';
        public const USER_AGENT = 'User-Agent';
        public const VARY = 'Vary';
        public const VIA = 'Via';
        public const WARNING = 'Warning';
        public const WWW_AUTHENTICATE = 'WWW-Authenticate';
        //Custom but important headers
        public const X_FRAME_OPTIONS = 'X-Frame-Options';
        public const X_CONTENT_TYPE_OPTIONS = 'X-Content-Type-Options';

        private array $headers = [];

        public function __construct(?array $headers = null)
        {
            if (!empty($headers)) {
                $this->setAll($headers);
            }
        }

        /**
         * Set the value of the Authorization header to Basic Authentication based
         * on the given username and password.
         * @param string $username the username
         * @param string $password the password
         * @return self
         */
        public function setBasicAuth(string $username, string $password): self
        {
            $this->headers[self::AUTHORIZATION] = 'Basic ' . base64_encode($username . ':' . $password);
            return $this;
        }

        /**
         * Set the value of the Authorization header to the given Bearer token.
         * @param string $token the Base64 encoded token
         * @return self
         */
        public function setBearerAuth(string $token): self
        {
            $this->headers[self::AUTHORIZATION] = 'Bearer ' . $token;
            return $this;
        }

        /**
         * Remove the well-known 'Content-*' HTTP headers.
         * <p>Such headers should be cleared from the response if the intended
         * body can't be written due to errors.</p>
         * @return self
         */
        public function clearContentHeaders(): self
        {
            unset($this->headers[HttpHeader::CONTENT_DISPOSITION]);
            unset($this->headers[HttpHeader::CONTENT_ENCODING]);
            unset($this->headers[HttpHeader::CONTENT_LANGUAGE]);
            unset($this->headers[HttpHeader::CONTENT_LENGTH]);
            unset($this->headers[HttpHeader::CONTENT_LOCATION]);
            unset($this->headers[HttpHeader::CONTENT_RANGE]);
            unset($this->headers[HttpHeader::CONTENT_TYPE]);
            return $this;
        }

        /**
         * Set the given, single header value under the given name only if it does not exists.
         * @param string $headerName the header name
         * @param array|string|null $headerValue the header value
         * @see set(string, string)
         * @return self
         */
        public function setIfAbsent(string $headerName, array|string|null $headerValue): self
        {
            if (!array_key_exists($headerName, $this->headers)) {
                $this->set($headerName, $headerValue);
            }
            return $this;
        }

        /**
         * Set the given, single header value under the given name.
         * @param string $headerName the header name
         * @param array|string|null $headerValue the header value
         * @see setIfAbsent(string, string)
         */
        public function set(string $headerName, array|string|null $headerValue): self
        {
            if ($headerValue === null) {
                return $this;
            }
            $name = self::createName($headerName);
            if (is_string($headerValue)) {
                $this->headers[$name] = $headerValue;
            } else {
                foreach ($headerValue as $key => $value) {
                    $this->headers[$name][$key] = $value;
                }
            }
            return $this;
        }

        public function setAll(array $headers): self
        {
            foreach ($headers as $name => $value) {
                $this->set($name, $value);
            }
            return $this;
        }

        public static function createName($headerName): string
        {
            return ucwords(strtolower($headerName), '-');
        }

        public function count(): int
        {
            return count($this->headers);
        }

        public function size(): int
        {
            return mb_strlen($this);
        }

        public function __toString(): string
        {
            $headerString = '';
            foreach ($this->headers as $key => $value) {
                $val = is_array($value) ? implode(',', $value) : $value;
                $headerString .= "\r\n" . $key = ': ' . $val;
            }
            return ltrim($headerString);
        }

        public function isEmpty(): bool
        {
            return $this->count() === 0;
        }

        public function has(string $headerName): bool
        {
            return array_key_exists($headerName, $this->headers);
        }

        public function getAll(array $headerNames = null): array
        {
            if (empty($headerNames)) {
                return $this->headers;
            }
            $names = [];
            foreach ($headerNames as $name) {
                $names[$name] = $this->get($name);
            }
            return $names;
        }

        public function get(string $headerName): ?string
        {
            return $this->headers[$headerName] ?? null;
        }

        public function remove(string $headerName): self
        {
            unset($this->headers[$headerName]);
            return $this;
        }

        public function removeAll(array $headerName): self
        {
            foreach ($headerName as $name) {
                $this->remove($name);
            }
            return $this;
        }

        public function clear(): self
        {
            unset($this->headers);
            return $this;
        }

        public function keySet(): array
        {
            return array_keys($this->headers);
        }

        public function values(): array
        {
            return array_values($this->headers);
        }

        public function entrySet(): array
        {
            $entries = [];
            foreach ($this->headers as $key => $value) {
                $entries[] = [$key => $value];
            }
            return $entries;
        }
    }

}
