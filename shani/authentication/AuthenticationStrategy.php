<?php

/**
 * Description of AuthenticationStrategy
 * @author goddy
 *
 * Created on: Apr 7, 2026 at 9:14:56 AM
 */

namespace shani\authentication {

    interface AuthenticationStrategy
    {

        /**
         * Authenticate user with the given credentials.
         * @return UserDetailsDto|null Authenticated user details on success, null otherwise.
         */
        public function authenticate(): ?UserDetailsDto;
    }

}
