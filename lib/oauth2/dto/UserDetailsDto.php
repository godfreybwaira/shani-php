<?php

/**
 * End-user who can log in and authorize applications.
 * @author goddy
 *
 * Created on: Mar 16, 2026 at 2:33:41 PM
 */

namespace lib\oauth2\dto {

    final class UserDetailsDto
    {

        /** @var int Internal user ID */
        public readonly string $id;

        /** @var string Username (unique) */
        public readonly string $username;

        /** @var string Hashed password (never store plaintext) */
        public readonly string $password;

        /**
         * @param string    $id        Internal database user ID.
         * @param string $username  Unique username for login.
         * @param string $password  Hashed password (password_hash() result).
         */
        public function __construct(string $id, string $username, string $password)
        {
            $this->id = $id;
            $this->username = $username;
            $this->password = $password;
        }
    }

}
