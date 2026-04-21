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
        public function login(): ?UserDetailsDto;

        /**
         * End user session
         * @return bool True on success, false otherwise.
         */
        public function logout(): bool;

        /**
         * Register a new user.
         * @return UserDetailsDto|null Registered user details on success, null if registration failed.
         */
        public function register(): ?UserDetailsDto;

        /**
         * Revoke (or delete) user registration
         * @return bool True on success, false otherwise.
         */
        public function unregister(): bool;

        /**
         * Update user details.
         * @return UserDetailsDto|null New Updated details on success, null if update failed.
         */
        public function update(): ?UserDetailsDto;
    }

}
