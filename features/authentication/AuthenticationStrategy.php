<?php

/**
 * Description of AuthenticationStrategy
 * @author goddy
 *
 * Created on: Apr 7, 2026 at 9:14:56 AM
 */

namespace features\authentication {

    interface AuthenticationStrategy
    {

        /**
         * Authenticate user with the given credentials.
         * @return UserDetailsDto|null Authenticated user details on success, null if authentication failed.
         */
        public function authenticate(): ?UserDetailsDto;

        /**
         * Register a new user.
         * @return UserDetailsDto|null Registered user details on success, null if registration failed.
         */
        public function register(): ?UserDetailsDto;

        /**
         * Update user details.
         * @return UserDetailsDto|null New Updated details on success, null if update failed.
         */
        public function update(): ?UserDetailsDto;
    }

}
