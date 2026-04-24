<?php

/**
 * Description of CsrfPresets
 * @author goddy
 *
 * Created on: Apr 24, 2026 at 2:51:54 PM
 */

namespace shani\presets {

    /**
     * Defines default Cross-Site Request Forgery (CSRF) protection policies.
     *
     * This class centralizes configuration for CSRF handling in an application.
     * It allows customization of:
     * - Whether CSRF protection is enabled
     * - The name of the CSRF token expected in requests
     * - Which HTTP methods are allowed to bypass CSRF checks
     * - Whether the CSRF test should be skipped for a given request
     *
     * By default:
     * - CSRF protection is enabled
     * - Token name is "X-Csrf-Token"
     * - Allowed methods are "get,head,options"
     * - SkipTest is computed based on whether CSRF is disabled or the request method is in allowed methods
     */
    final class CsrfPresets
    {

        /**
         * Enable/Disable CSRF check.
         *
         * @var bool
         */
        public readonly bool $enabled;

        /**
         * Token name holding a CSRF token on an incoming request.
         *
         * @var string
         */
        public readonly string $tokenName;

        /**
         * List of HTTP methods that are allowed to bypass CSRF check.
         *
         * @var string
         */
        public readonly string $allowedMethods;

        /**
         * Whether to continue with CSRF test by checking on request method or to stop.
         *
         * @var string
         */
        public readonly string $skipTest;

        /**
         * Constructor for CsrfPresets.
         *
         * Initializes CSRF protection configuration with defaults if none are provided.
         *
         * @param string $requestMethod
         *     The HTTP request method being checked (e.g., 'post', 'get').
         *
         * @param bool $enabled
         *     Whether CSRF protection is enabled. Defaults to true.
         *
         * @param string $tokenName
         *     The name of the CSRF token expected in requests. Defaults to 'X-Csrf-Token'.
         *
         * @param string $allowedMethods
         *     Comma-separated list of HTTP methods that bypass CSRF checks.
         *     Defaults to 'get,head,options'.
         */
        public function __construct(
                string $requestMethod,
                bool $enabled = true,
                string $tokenName = 'X-Csrf-Token',
                string $allowedMethods = 'get,head,options'
        )
        {
            $this->enabled = $enabled;
            $this->tokenName = $tokenName;
            $this->allowedMethods = $allowedMethods;
            $this->skipTest = !$enabled || str_contains($allowedMethods, $requestMethod);
        }
    }

}
