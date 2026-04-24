<?php

/**
 * Description of AuthenticationPresets
 * @author goddy
 *
 * Created on: Apr 24, 2026 at 12:31:28 PM
 */

namespace shani\presets {

    /**
     * Defines default authentication policies for an application.
     *
     * This class centralizes configuration for user authentication handling.
     * It allows customization of:
     * - Whether authentication should be skipped entirely (not recommended in production)
     * - A list of authentication strategies implementing the AuthenticationStrategy interface
     *
     * Developers must implement the AuthenticationStrategy interface to provide
     * custom authentication logic (e.g., token-based, session-based, OAuth).
     *
     * By default:
     * - Authentication is required (skipAuthentication = false)
     * - No authentication strategies are defined (empty array)
     */
    final class AuthenticationPresets
    {

        /**
         * Enable/disable user authorization on restricted resources.
         * (Permanent skipping authorization is not recommended in production environment)
         *
         * @var bool
         */
        public readonly bool $skipAuthentication;

        /**
         * Authentication strategies implementing the AuthenticationStrategy interface.
         * Developers must implement this interface to provide authentication logic.
         *
         * @var AuthenticationStrategy[]
         */
        public readonly array $authenticationStrategies;

        /**
         * Constructor for AuthenticationPresets.
         *
         * Initializes authentication configuration with defaults if none are provided.
         *
         * @param bool $skipAuthentication
         *     Whether to skip authentication checks. Defaults to false.
         *
         * @param AuthenticationStrategy[] $authenticationStrategies
         *     A list of authentication strategy objects implementing AuthenticationStrategy.
         *     Defaults to an empty array.
         */
        public function __construct(bool $skipAuthentication = false, array $authenticationStrategies = [])
        {
            $this->skipAuthentication = $skipAuthentication;
            $this->authenticationStrategies = $authenticationStrategies;
        }
    }

}
