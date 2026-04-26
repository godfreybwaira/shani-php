<?php

/**
 * Description of StaticAssetOwnership
 * @author goddy
 *
 * Created on: Apr 26, 2026 at 8:03:15 PM
 */

namespace shani\assets {

    use features\authentication\UserDetailsDto;
    use features\exceptions\ServerException;

    final class StaticAssetOwnership
    {

        private const ID_SEPARATOR = '_', GID_PREFIX = 'g', UID_PREFIX = 'u';

        //u122_g233_filename.png
        public readonly string $filename; //filename.png
        public readonly ?string $userBucket; //122
        public readonly ?string $groupBucket; //233

        public function __construct(string $filename)
        {
            $values = explode(self::ID_SEPARATOR, basename($filename));
            $this->filename = $values[count($values) - 1];
            $this->userBucket = self::createBucket($values[0], self::UID_PREFIX);
            $this->groupBucket = self::createBucket($values[1] ?? null, self::GID_PREFIX);
        }

        private static function createBucket(?string $value, string $prefix): ?string
        {
            return isset($value) && str_starts_with($value, $prefix) ? substr($value, strlen($prefix)) : null;
        }

        public function hasAccess(UserDetailsDto $user): bool
        {
            return $this->isOwner($user) || $this->groupBucket === $user->groupStorageBucket || empty($this->groupBucket);
        }

        public function isOwner(UserDetailsDto $user): bool
        {
            return $this->userBucket === $user->storageBucket;
        }

        public static function createPrivateFilePrefix(?string $userBucket): string
        {
            if (empty($userBucket)) {
                throw new ServerException('Client private bucket cannot be empty.');
            }
            return self::UID_PREFIX . $userBucket . self::ID_SEPARATOR;
        }

        public static function createProtectedFilePrefix(?string $userBucket): string
        {
            $prefix = self::createPrivateFilePrefix($userBucket) . self::GID_PREFIX;
            return $prefix . self::ID_SEPARATOR;
        }

        public static function createGroupFilePrefix(?string $userBucket, string $groupBucket): string
        {
            $prefix = self::createPrivateFilePrefix($userBucket) . self::GID_PREFIX;
            return $prefix . $groupBucket . self::ID_SEPARATOR;
        }
    }

}
