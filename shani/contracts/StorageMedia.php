<?php

/**
 * Description of StorageMedia
 * @author coder
 *
 * Created on: Mar 27, 2025 at 5:29:05 PM
 */

namespace shani\contracts {

    use features\utils\File;
    use features\utils\URI;

    interface StorageMedia
    {

        /**
         * Save a file with a private access. A private file is accessible
         * only by a file owner
         * @param File $file A file object to save
         * @param string $bucket A destination directory
         * @param bool $rename Whether to rename the file or not (recommended)
         * @return string|null Path to a saved file.
         */
        public function save(File $file, string $bucket = '/', bool $rename = true): string;

        /**
         * Get asset file URI
         * @param string $filepath File path
         * @return URI A URL referring to a file from a storage media
         */
        public function assetUri(string $filepath): URI;

        /**
         * Get file URI
         * @param string $filepath File path
         * @return URI A URL referring to a file from a storage media
         */
        public function uri(string $filepath): URI;

        /**
         * Delete a file
         * @param string $filepath File path
         * @return bool True on success, false otherwise.
         */
        public function delete(string $filepath): bool;

        /**
         * Copy a file to a public directory. If the file is already in public
         * directory nothing will happens
         * @param string $filepath Source file to copy
         * @return string|null A path to a new location, or null otherwise
         */
        public function share2public(string $filepath): ?string;

        /**
         * Copy a file to a protected directory. If the file is already in protected
         * directory nothing will happen
         * @param string $filepath Source file to copy
         * @return string|null A path to a new location, or null otherwise
         */
        public function share2protected(string $filepath): ?string;

        /**
         * Copy a file to a shared directory. If the file is already in private
         * directory nothing will happen
         * @param string $filepath Source file to copy
         * @param string $groupId Client group Id to save in
         * @return string|null A path to a new location, or null otherwise
         */
        public function share2group(string $filepath, string $groupId): ?string;

        /**
         * Copy a file to a shared directory. If the file is already in private
         * directory nothing will happen
         * @param string $filepath Source file to copy
         * @param string $otherId Client group Id to save in
         * @return string|null A path to a new location, or null otherwise
         */
        public function share2other(string $filepath, string $otherId): ?string;

        /**
         * Get a full path to a storage destination
         * @param string|null $path File or a directory
         * @return string Path to a storage destination (endpoint)
         */
        public function pathTo(?string $path = null): string;
    }

}
