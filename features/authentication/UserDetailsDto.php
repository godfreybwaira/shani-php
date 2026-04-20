<?php

/**
 * Registered system end-user.
 * @author goddy
 *
 * Created on: Mar 16, 2026 at 2:33:41 PM
 */

namespace features\authentication {

    final class UserDetailsDto
    {

        /**
         * Internal user ID
         * @var string
         */
        public readonly string $id;

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
         * @param string|null   $permissions    Granted user permissions.
         * @param bool          $isDisabled     Tells whether the client is disabled or not
         */
        public function __construct(string $id, ?string $permissions, bool $isDisabled)
        {
            $this->id = $id;
            $this->permissions = $permissions;
            $this->isDisabled = $isDisabled;
        }
    }

}
