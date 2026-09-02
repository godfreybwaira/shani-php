<?php

/**
 * Description of AuthenticationStrategy
 * @author goddy
 *
 * @since Apr 7, 2026 at 9:14:56 AM
 */

namespace features\authentication {

    interface AuthenticationStrategy
    {

        /**
         * Authenticate user with the given credentials.
         * @return AuthenticationResult|null Authentication result on success, null if authentication failed.
         */
        public function login(): ?AuthenticationResult;

        /**
         * End user session
         * @return bool True on success, false otherwise.
         */
        public function logout(): bool;
    }

}
