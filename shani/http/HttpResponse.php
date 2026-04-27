<?php

/**
 * Description of HttpResponse
 * @author goddy
 *
 * Created on: Apr 25, 2026 at 2:56:45 PM
 */

namespace shani\http {

    use gui\WebUIBuilder;
    use shani\http\enums\HttpConnection;

    /**
     * Represents an HTTP response with a body and connection details.
     */
    final class HttpResponse
    {

        /**
         * The response body.
         *
         * Can be one of the following types:
         * - \Closure: A callback function to generate the body dynamically
         * - \JsonSerializable: An object that can be serialized to JSON
         * - WebUIBuilder: A builder for rendering web UI components
         * - FileOutputStream: A stream for file output
         * - string: A plain string response body
         *
         * @var \Closure|\JsonSerializable|WebUIBuilder|FileOutputStream|string
         */
        public readonly \Closure|\JsonSerializable|WebUIBuilder|FileOutputStream|string $body;

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
         * @param \Closure|\JsonSerializable|WebUIBuilder|FileOutputStream|string $body
         *        The response body, which may be a closure, JSON-serializable object,
         *        web UI builder, file output stream, or string.
         * @param HttpConnection|null $connection
         *        The HTTP connection. If null, defaults to HttpConnection::AUTO.
         */
        public static function withBody(
                \Closure|\JsonSerializable|WebUIBuilder|FileOutputStream|string $body,
                HttpConnection $connection = null): HttpResponse
        {
            return new HttpResponse($body, $connection ?? HttpConnection::AUTO);
        }
    }

}
