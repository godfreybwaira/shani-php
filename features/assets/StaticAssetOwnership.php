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
    use shani\config\PathConfig;
    use shani\http\RequestRoute;

    /**
     * Represents ownership information for a static asset file.
     *
     * The filename encodes ownership details using separator '_'
     *
     * Example encoded filename:
     *   u16e9a5ecb65264ebbfd_g79a7ac18440680f461b_fff5e770fd6ad63d9a.png
     */
    final class StaticAssetOwnership
    {

        /** @var string Separator used between identifiers in filenames */
        private const SEPARATOR = '_';

        /**
         * Creates a private file prefix for a user bucket.
         *
         * @param string|null $userBucket The user bucket identifier.
         *
         * @return string The generated prefix (e.g., "u12345_").
         *
         * @throws ServerException If the user bucket is empty.
         */
        public static function createUserFilePrefix(?string $userBucket): string
        {
            if (empty($userBucket)) {
                throw new ServerException('User bucket cannot be empty.');
            }
            return $userBucket . self::SEPARATOR;
        }

        /**
         * Creates a group file prefix for a user and group bucket.
         *
         * @param string|null   $userBucket  The user bucket identifier.
         * @param string|null   $groupBucket The group bucket identifier.
         *
         * @return string The generated prefix (e.g., "u12345_g67890_" or "u12345__").
         */
        public static function createGroupFilePrefix(?string $userBucket, ?string $groupBucket = null): string
        {
            return self::createUserFilePrefix($userBucket) . $groupBucket . self::SEPARATOR;
        }

        /**
         * Generates a random bucket name.
         *
         * @param int $min Minimum length of the bucket name (default: 10).
         * @param int $max Maximum length of the bucket name (default: 15).
         *
         * @return string A random bucket name consisting of hex characters.
         *
         * @throws Exception If random_bytes or random_int fails.
         */
        public static function createBucketName(int $min = 10, int $max = 15): string
        {
            return substr(bin2hex(random_bytes(random_int(10, 70))), 0, rand($min, $max));
        }

        /**
         * Checks if the current request is for a public resource.
         *
         * @param string $filename File name
         * @param RequestRoute $route Request Route object
         * @param PathConfig $config Path config object
         *
         * @return bool True if public, false otherwise.
         */
        public static function isPublicResource(string $filename, RequestRoute $route, PathConfig $config): bool
        {
            $prefix = '/' . $route->module;
            if ($prefix === $config->privateBucket) {
                return strpos(basename($filename), self::SEPARATOR . self::SEPARATOR) > 0;
            }
            return false;
        }

        /**
         * Determines if the given user has access to the requested asset.
         *
         * Access is granted if:
         * - The user is the owner, OR
         * - The user's group bucket matches the asset's group bucket, OR
         * - The asset has no group bucket restriction.
         *
         * @param UserDetailsDto $user The user details.
         * @param string $filename The filename of the requested asset
         *
         * @return bool True if the user has access, false otherwise.
         */
        public static function hasAccess(UserDetailsDto $user, string $filename): bool
        {
            $name = basename($filename);
            if (strpos($name, self::SEPARATOR . self::SEPARATOR) > 0) {
                return true;
            }
            return self::isOwner($user, $name) || strpos($name, self::SEPARATOR . $user->groupStorageBucket . self::SEPARATOR) > 0;
        }

        /**
         * Checks if the given user is the owner of this asset.
         *
         * @param UserDetailsDto $user The user details.
         * @param string $filename The filename of the requested asset
         *
         * @return bool True if the user is the owner, false otherwise.
         */
        public STATIC function isOwner(UserDetailsDto $user, string $filename): bool
        {
            return str_starts_with(basename($filename), $user->storageBucket . self::SEPARATOR);
        }
    }

}
