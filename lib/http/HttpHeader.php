<?php

namespace lib\http {

    use lib\IterableData;

    final class HttpHeader extends IterableData
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

        public function __construct(?array $headers = null)
        {
            parent::__construct([]);
            if (!empty($headers)) {
                $this->addAll($headers);
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
            return parent::add(self::AUTHORIZATION, 'Basic ' . base64_encode($username . ':' . $password));
        }

        /**
         * Set the value of the Authorization header to the given Bearer token.
         * @param string $token the Base64 encoded token
         * @return self
         */
        public function setBearerAuth(string $token): self
        {
            return parent::add(self::AUTHORIZATION, 'Bearer ' . $token);
        }

        /**
         * Remove the well-known 'Content-*' HTTP headers.
         * <p>Such headers should be cleared from the response if the intended
         * body can't be written due to errors.</p>
         * @return self
         */
        public function clearContentHeaders(): self
        {
            parent::delete(HttpHeader::CONTENT_DISPOSITION);
            parent::delete(HttpHeader::CONTENT_ENCODING);
            parent::delete(HttpHeader::CONTENT_LANGUAGE);
            parent::delete(HttpHeader::CONTENT_LENGTH);
            parent::delete(HttpHeader::CONTENT_LOCATION);
            parent::delete(HttpHeader::CONTENT_RANGE);
            return parent::delete(HttpHeader::CONTENT_TYPE);
        }

        /**
         * Set the given, single header value under the given name only if it does not exists.
         * @param string|int $headerName the header name
         * @param mixed $headerValue the header value
         * @see set(string, string)
         * @return self
         */
        public function addIfAbsent(string|int $headerName, mixed $headerValue): self
        {
            if (!array_key_exists($headerName, $this->data)) {
                $this->add($headerName, $headerValue);
            }
            return $this;
        }

        /**
         * Set the given, single header value under the given name.
         * @param string|int $headerName the header name
         * @param mixed $headerValue the header value
         * @see setIfAbsent(string, string)
         */
        public function add(string|int $headerName, mixed $headerValue): self
        {
            $name = self::createName($headerName);
            if (!is_array($headerValue)) {
                return parent::add($name, $headerValue);
            }
            foreach ($headerValue as $key => $value) {
                $this->data[$name][$key] = $value;
            }
            return $this;
        }

        public function addAll(array $headers): self
        {
            foreach ($headers as $name => $value) {
                $this->add($name, $value);
            }
            return $this;
        }

        public static function createName($headerName): string
        {
            return ucwords(strtolower($headerName), '-');
        }

        public function size(): int
        {
            return mb_strlen($this);
        }

        #[\Override]
        public function __toString(): string
        {
            $headerString = '';
            foreach ($this->data as $key => $value) {
                $val = is_array($value) ? implode(',', $value) : $value;
                $headerString .= "\r\n" . $key = ': ' . $val;
            }
            return ltrim($headerString);
        }
    }

}
