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
         * @param string $bucket A destination directory name.
         *
         * @return string Path to the saved file.
         */
        public function save(File $file, string $bucket = null): string;

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
         * @param File $file File object.
         *
         * @return bool True on success, false otherwise.
         */
        public function delete(File $file): bool;

        /**
         * Create a shortcut of a file to a specified group. If group bucket is
         * null then the file become publicly accessible.
         *
         * @param File      $file           Source file to copy.
         * @param string    $groupBucket    Group bucket to save in.
         *
         * @return string   Path to new location.
         */
        public function share2group(File $file, string $groupBucket = null): string;

        /**
         * Create a shortcut of a file to another user.
         *
         * @param File      $file           Source file to copy.
         * @param string    $userBucket     Other user bucket name to save in.
         *
         * @return string   Path to new location.
         */
        public function share2other(File $file, string $userBucket): string;

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
