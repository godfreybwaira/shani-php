<?php

/**
 * End-user who can log in and authorize applications.
 * @author goddy
 *
 * Created on: Mar 16, 2026 at 2:33:41 PM
 */

namespace shani\authentication {

    final class UserDetailsDto
    {

        /**
         * Internal user ID
         * @var string
         */
        public readonly string $id;

        /**
         * Username (unique)
         * @var string
         */
        public readonly string $username;

        /**
         * Hashed password
         * @var string
         */
        public readonly string $passwordHash;

        /**
         * User permissions
         * @var string|null
         */
        public readonly ?string $permissions;

        /**
         * Tells whether the user is disabled or not
         * @var bool
         */
        public readonly bool $isDisabled;

        /**
         * @param string        $id             Internal database user ID.
         * @param string        $username       Unique username for login.
         * @param string        $passwordHash   Hashed password (password_hash() result).
         * @param string|null   $permissions    Granted user permissions.
         * @param bool          $isDisabled     Tells whether the client is disabled or not
         */
        public function __construct(string $id, string $username, string $passwordHash, ?string $permissions, bool $isDisabled)
        {
            $this->id = $id;
            $this->username = $username;
            $this->passwordHash = $passwordHash;
            $this->permissions = $permissions;
            $this->isDisabled = $isDisabled;
        }
    }

}
