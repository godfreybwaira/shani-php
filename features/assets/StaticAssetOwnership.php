<?php

/**
 * Description of StaticAssetOwnership
 * @author goddy
 *
 * Created on: Apr 26, 2026 at 8:03:15 PM
 */

namespace features\assets {

    use features\authentication\UserDetailsDto;
    use features\exceptions\ServerException;

    /**
     * Represents ownership information for a static asset file.
     *
     * The filename encodes ownership details using prefixes:
     * - User bucket: prefixed with "u"
     * - Group bucket: prefixed with "g"
     *
     * Example encoded filename:
     *   u16e9a5ecb65264ebbfd_g79a7ac18440680f461b_fff5e770fd6ad63d9a.png
     */
    final class StaticAssetOwnership
    {

        /** @var string Separator used between identifiers in filenames */
        private const ID_SEPARATOR = '_';

        /** @var string Prefix for group bucket identifiers */
        private const GID_PREFIX = 'g';

        /** @var string Prefix for user bucket identifiers */
        private const UID_PREFIX = 'u';

        /** @var string The actual filename (without ownership prefixes) */
        public readonly string $filename;

        /** @var string|null The user storage bucket identifier, if present */
        public readonly ?string $userBucket;

        /** @var string|null The group storage bucket identifier, if present */
        public readonly ?string $groupBucket;

        /**
         * Constructor.
         *
         * Parses the given filename to extract ownership information.
         *
         * @param string $filename Encoded filename containing ownership prefixes.
         *                         Example: "u16e9a5ecb65264ebbfd_g79a7ac18440680f461b_fff5e770fd6ad63d9a.png"
         */
        public function __construct(string $filename)
        {
            $values = explode(self::ID_SEPARATOR, basename($filename));
            $this->filename = $values[count($values) - 1];
            $this->userBucket = self::getBucket($values[0], self::UID_PREFIX);
            $this->groupBucket = self::getBucket($values[1] ?? null, self::GID_PREFIX);
        }

        /**
         * Extracts a bucket identifier from a prefixed value.
         *
         * @param string|null $value  The prefixed value (e.g., "u12345").
         * @param string      $prefix The expected prefix ("u" or "g").
         *
         * @return string|null The bucket identifier without prefix, or null if not valid.
         */
        private static function getBucket(?string $value, string $prefix): ?string
        {
            return isset($value) && str_starts_with($value, $prefix) ? substr($value, strlen($prefix)) : null;
        }

        /**
         * Determines if the given user has access to this asset.
         *
         * Access is granted if:
         * - The user is the owner, OR
         * - The user's group bucket matches the asset's group bucket, OR
         * - The asset has no group bucket restriction.
         *
         * @param UserDetailsDto $user The user details.
         *
         * @return bool True if the user has access, false otherwise.
         */
        public function hasAccess(UserDetailsDto $user): bool
        {
            return $this->isOwner($user) || $this->groupBucket === $user->groupStorageBucket || empty($this->groupBucket);
        }

        /**
         * Checks if the given user is the owner of this asset.
         *
         * @param UserDetailsDto $user The user details.
         *
         * @return bool True if the user is the owner, false otherwise.
         */
        public function isOwner(UserDetailsDto $user): bool
        {
            return $this->userBucket === $user->storageBucket;
        }

        /**
         * Creates a private file prefix for a user bucket.
         *
         * @param string|null $userBucket The user bucket identifier.
         *
         * @return string The generated prefix (e.g., "u12345_").
         *
         * @throws ServerException If the user bucket is empty.
         */
        public static function createPrivateFilePrefix(?string $userBucket): string
        {
            if (empty($userBucket)) {
                throw new ServerException('Client private bucket cannot be empty.');
            }
            return self::UID_PREFIX . $userBucket . self::ID_SEPARATOR;
        }

        /**
         * Creates a protected file prefix for a user bucket.
         *
         * @param string|null $userBucket The user bucket identifier.
         *
         * @return string The generated prefix (e.g., "u12345_g_").
         */
        public static function createProtectedFilePrefix(?string $userBucket): string
        {
            $prefix = self::createPrivateFilePrefix($userBucket) . self::GID_PREFIX;
            return $prefix . self::ID_SEPARATOR;
        }

        /**
         * Creates a group file prefix for a user and group bucket.
         *
         * @param string|null $userBucket  The user bucket identifier.
         * @param string      $groupBucket The group bucket identifier.
         *
         * @return string The generated prefix (e.g., "u12345_g67890_").
         */
        public static function createGroupFilePrefix(?string $userBucket, string $groupBucket): string
        {
            $prefix = self::createPrivateFilePrefix($userBucket) . self::GID_PREFIX;
            return $prefix . $groupBucket . self::ID_SEPARATOR;
        }

        /**
         * Generates a random bucket name.
         *
         * @param int $min Minimum length of the bucket name (default: 10).
         * @param int $max Maximum length of the bucket name (default: 20).
         *
         * @return string A random bucket name consisting of hex characters.
         *
         * @throws Exception If random_bytes or random_int fails.
         */
        public static function createBucketName(int $min = 10, int $max = 20): string
        {
            return substr(bin2hex(random_bytes(random_int(10, 70))), 0, rand($min, $max));
        }
    }

}
