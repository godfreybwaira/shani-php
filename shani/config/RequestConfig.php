<?php

/**
 * Description of RequestConfig
 * @author goddy
 *
 * Created on: Apr 24, 2026 at 3:21:06 PM
 */

namespace shani\config {

    /**
     * Defines default request handling policies for an application.
     *
     * This class provides a convenient way to configure which HTTP methods and headers
     * are allowed by the application. It also determines whether a given request method
     * is permitted based on the configured rules.
     *
     * Key features:
     * - Allowed HTTP methods (comma-separated list or wildcard '*')
     * - Allowed HTTP headers (comma-separated list or wildcard '*')
     * - Boolean flag indicating if the current request method is permitted
     *
     * By default:
     * - Allowed methods: '*' (all methods allowed)
     * - Allowed headers: '*' (all headers allowed)
     * - MethodAllowed is computed based on the request method and allowed methods
     */
    final class RequestConfig
    {

        /**
         * A list of HTTP request methods supported by the application
         * (in lower case) separated by a comma, or '*' for all.
         *
         * @var string
         */
        public readonly string $allowedMethods;

        /**
         * A list of HTTP request headers supported by the application
         * (in lower case) separated by a comma, or '*' for all.
         *
         * @var string
         */
        public readonly string $allowedHeaders;

        /**
         * Indicates whether the given request method is allowed
         * based on the configured allowed methods.
         *
         * @var bool
         */
        public readonly bool $methodAllowed;

        /**
         * Constructor for RequestConfig.
         *
         * Initializes request handling policies with defaults if none are provided.
         *
         * @param string $requestMethod
         *     The HTTP request method being checked (e.g., 'get', 'post').
         *
         * @param string $allowedMethods
         *     Comma-separated list of allowed HTTP methods (in lower case),
         *     or '*' to allow all methods. Defaults to '*'.
         *
         * @param string $allowedHeaders
         *     Comma-separated list of allowed HTTP headers (in lower case),
         *     or '*' to allow all headers. Defaults to '*'.
         */
        public function __construct(string $requestMethod, string $allowedMethods = '*', string $allowedHeaders = '*')
        {
            $this->allowedMethods = $allowedMethods;
            $this->allowedHeaders = $allowedHeaders;
            $this->methodAllowed = $allowedMethods === '*' || str_contains($allowedMethods, $requestMethod);
        }
    }

}
