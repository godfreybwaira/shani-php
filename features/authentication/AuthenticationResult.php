<?php

/**
 * Represents the outcome of an authentication attempt.
 * Holds the authenticated user's details and whether the user should be remembered
 *
 * @author goddy
 * @created May 27, 2026 at 1:48:02 PM
 */

namespace features\authentication {

    final class AuthenticationResult
    {

        /**
         * The authenticated user's details.
         *
         * @var UserDetailsDto
         */
        public readonly UserDetailsDto $user;

        /**
         * Whether the user should be remembered (i.e. persistent login). If set
         * to false, then user has to be authenticated on every request.
         *
         * @var bool
         */
        public readonly bool $rememberUser;

        /**
         * Create a new authentication result.
         *
         * @param UserDetailsDto $user The authenticated user's details.
         * @param bool $rememberUser Whether the user should be remembered.
         * If set to false, then user has to be authenticated on every request.
         */
        public function __construct(UserDetailsDto $user, bool $rememberUser)
        {
            $this->user = $user;
            $this->rememberUser = $rememberUser;
        }
    }

}
