<?php

/**
 *
 * @author coder
 */

namespace lib\http {

    enum HttpStatus: int
    {

        case CONTINUE = 100;
        case SWITCHING_PROTOCOLS = 101;
        case PROCESSING = 102;
        case OK = 200;
        case CREATED = 201;
        case ACCEPTED = 202;
        case NON_AUTHORITATIVE_INFORMATION = 203;
        case NO_CONTENT = 204;
        case RESET_CONTENT = 205;
        case PARTIAL_CONTENT = 206;
        case MULTI_STATUS = 207;
        case MULTIPLE_CHOICES = 300;
        case MOVED_PERMANENTLY = 301;
        case FOUND = 302;
        case SEE_OTHER = 303;
        case NOT_MODIFIED = 304;
        case USE_PROXY = 305;
        case TEMPORARY_REDIRECT = 307;
        case BAD_REQUEST = 400;
        case UNAUTHORIZED = 401;
        case PAYMENT_REQUIRED = 402;
        case FORBIDDEN = 403;
        case NOT_FOUND = 404;
        case METHOD_NOT_ALLOWED = 405;
        case NOT_ACCEPTABLE = 406;
        case PROXY_AUTHENTICATION_REQUIRED = 407;
        case REQUEST_TIMEOUT = 408;
        case CONFLICT = 409;
        case GONE = 410;
        case LENGTH_REQUIRED = 411;
        case PRECONDITION_FAILED = 412;
        case REQUEST_ENTITY_TOO_LARGE = 413;
        case REQUEST_URI_TOO_LONG = 414;
        case UNSUPPORTED_MEDIA_TYPE = 415;
        case REQUESTED_RANGE_NOT_SATISFIABLE = 416;
        case EXPECTATION_FAILED = 417;
        case UNPROCESSABLE_ENTITY = 422;
        case LOCKED = 423;
        case FAILED_DEPENDENCY = 424;
        case UPGRADE_REQUIRED = 426;
        case INTERNAL_SERVER_ERROR = 500;
        case NOT_IMPLEMENTED = 501;
        case BAD_GATEWAY = 502;
        case SERVICE_UNAVAILABLE = 503;
        case GATEWAY_TIMEOUT = 504;
        case HTTP_VERSION_NOT_SUPPORTED = 505;
        case VARIANT_ALSO_NEGOTIATES = 506;
        case INSUFFICIENT_STORAGE = 507;
        case BANDWIDTH_LIMIT_EXCEEDED = 509;
        case NOT_EXTENDED = 510;

        public function getMessage(): string
        {
            $name = strtolower(str_replace('_', ' ', $this->name));
            return ucfirst($name);
        }

        public function is1xx(): bool
        {
            return $this->value >= 100 && $this->value < 200;
        }

        public function is2xx(): bool
        {
            return $this->value >= 200 && $this->value < 300;
        }

        public function is3xx(): bool
        {
            return $this->value >= 300 && $this->value < 400;
        }

        public function is4xx(): bool
        {
            return $this->value >= 400 && $this->value < 500;
        }

        public function is5xx(): bool
        {
            return $this->value >= 500 && $this->value < 600;
        }

        public function isError(): bool
        {
            return $this->is4xx() || $this->is5xx();
        }
    }

}