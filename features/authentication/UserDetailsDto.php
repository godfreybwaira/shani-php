<?php

/**
 * Registered system end-user.
 * @author goddy
 *
 * Created on: Mar 16, 2026 at 2:33:41 PM
 */

namespace features\authentication {

    final class UserDetailsDto implements \JsonSerializable
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
         * User storage bucket (directory) for storing private files. This value
         * should not be changed anyhow, otherwise will loose access to their private uploaded files.
         * @var string|null
         */
        public readonly ?string $storageBucket;

        /**
         * User storage bucket (directory) for storing group (shared) files. This value
         * should not be changed anyhow, otherwise will loose access to their group uploaded files.
         * @var string|null
         */
        public readonly ?string $groupStorageBucket;

        /**
         * @param string        $id             Internal database user ID.
         * @param string|null   $permissions    Granted user permissions.
         * @param bool          $isDisabled     Tells whether the client is disabled or not
         * @param string|null   $userBucket     User storage bucket
         * @param string|null   $groupBucket    User group storage bucket
         */
        public function __construct(string $id, ?string $permissions, bool $isDisabled, ?string $userBucket = null, ?string $groupBucket = null)
        {
            $this->id = $id;
            $this->permissions = $permissions;
            $this->isDisabled = $isDisabled;
            $this->storageBucket = $userBucket;
            $this->groupStorageBucket = $groupBucket;
        }

        public static function fromArray(array $details): UserDetailsDto
        {
            return new self($details['id'], $details['permissions'] ?? null, $details['disabled'], $details['user_bucket'] ?? null, $details['group_bucket'] ?? null);
        }

        public function jsonSerialize(): array
        {
            return [
                'id' => $this->id,
                'permissions' => $this->permissions,
                'disabled' => $this->isDisabled,
                'user_bucket' => $this->storageBucket,
                'group_bucket' => $this->groupStorageBucket,
            ];
        }
    }

}
