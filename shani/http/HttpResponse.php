<?php

/**
 * Description of HttpResponse
 * @author goddy
 *
 * @since Apr 25, 2026 at 2:56:45 PM
 */

namespace shani\http {

    use shani\http\enums\HttpConnection;

    /**
     * Represents an HTTP response with a body and connection details.
     */
    final class HttpResponse
    {

        /**
         * The response body.
         *
         * @var mixed
         */
        public readonly mixed $body;

        /**
         * The HTTP connection associated with this response.
         *
         * Defaults to HttpConnection::AUTO if not provided.
         *
         * @var HttpConnection
         */
        public readonly HttpConnection $connection;

        private function __construct(mixed $body, HttpConnection $connection)
        {
            $this->body = $body;
            $this->connection = $connection;
        }

        /**
         * Constructs a HttpResponse::withBody instance.
         *
         * @param mixed $body The response body.
         * @param HttpConnection|null $connection The HTTP connection. If null, defaults to HttpConnection::AUTO.
         */
        public static function withBody(mixed $body, HttpConnection $connection = null): HttpResponse
        {
            return new HttpResponse($body, $connection ?? HttpConnection::AUTO);
        }
    }

}
