<?php

/**
 * Description of StorageMediaInterface
 * @author coder
 *
 * Created on: Mar 27, 2025 at 5:29:05 PM
 */

namespace features\storage {

    use features\utils\File;
    use features\utils\URI;

    /**
     * Defines the contract for storage media operations.
     *
     * This interface provides methods for saving, retrieving, deleting,
     * and sharing files across different access levels (private, protected, public, group).
     */
    interface StorageMediaInterface
    {

        /**
         * Save a file with private access. A private file is accessible only by its owner.
         *
         * @param File   $file   A file object to save.
         * @param string $bucket A destination directory.
         * @param bool   $rename Whether to rename the file or not (recommended).
         *
         * @return string Path to the saved file.
         */
        public function save(File $file, string $bucket = '/', bool $rename = true): string;

        /**
         * Get asset file URI.
         *
         * @param string $filepath File path.
         *
         * @return URI A URL referring to a file from storage media.
         */
        public function assetUri(string $filepath): URI;

        /**
         * Get file URI.
         *
         * @param string $filepath File path.
         *
         * @return URI A URL referring to a file from storage media.
         */
        public function uri(string $filepath): URI;

        /**
         * Delete a file.
         *
         * @param string $filepath File path.
         *
         * @return bool True on success, false otherwise.
         */
        public function delete(string $filepath): bool;

        /**
         * Copy a file to a public directory. If already public, nothing happens.
         *
         * @param string $filepath Source file to copy.
         *
         * @return string|null Path to new location, or null otherwise.
         */
        public function share2public(string $filepath): ?string;

        /**
         * Copy a file to a protected directory. If already protected, nothing happens.
         *
         * @param string $filepath Source file to copy.
         *
         * @return string|null Path to new location, or null otherwise.
         */
        public function share2protected(string $filepath): ?string;

        /**
         * Copy a file to a group-shared directory. If already private, nothing happens.
         *
         * @param string $filepath Source file to copy.
         *
         * @return string|null Path to new location, or null otherwise.
         */
        public function share2group(string $filepath): ?string;

        /**
         * Copy a file to another group's shared directory.
         *
         * @param string $filepath Source file to copy.
         * @param string $otherId  Client group ID to save in.
         *
         * @return string|null Path to new location, or null otherwise.
         */
        public function share2other(string $filepath, string $otherId): ?string;

        /**
         * Get a full path to a storage destination.
         *
         * @param string|null $path File or directory.
         *
         * @return string Path to storage destination (endpoint).
         */
        public function pathTo(?string $path = null): string;
    }

}
