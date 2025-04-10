<?php

/**
 * Description of StorageMedia
 * @author coder
 *
 * Created on: Mar 27, 2025 at 5:29:05 PM
 */

namespace shani\contracts {

    use lib\File;

    interface StorageMedia
    {

        /**
         * Save a file to a public directory. A public directory is accessible
         * by everyone
         * @param File $file A file object to save
         * @param string $bucket A destination directory
         * @return string|null Path to a File, null otherwise
         */
        public function save(File $file, string $bucket = '/'): ?string;

        /**
         * Save a file to a protected directory. A protected directory is accessible
         * by authenticated clients only
         * @param File $file A file object to save
         * @param string $bucket A destination directory
         * @return string|null Path to a File, null otherwise
         */
        public function saveProtect(File $file, string $bucket = '/'): ?string;

        /**
         * Save a file to a protected directory. This resource is only accessible
         * to authenticated clients with the same group Id
         * @param File $file A file object to save
         * @param string|null $bucket A destination directory
         * @return string Path to a File, null otherwise
         * @see \shani\advisors\Configuration::clientGroupId()
         */
        public function savePrivate(File $file, string $bucket = '/'): ?string;

        /**
         * Get file URL
         * @param string $filepath File path
         * @return string URL referring to a file from a storage media
         */
        public function url(string $filepath): string;

        /**
         * Download a file
         * @param string $filepath File path
         * @param string|null $filename Optional new file name
         * @return self
         */
        public function download(string $filepath, ?string $filename = null): self;

        /**
         * Delete a file
         * @param string $filepath File path
         * @return self
         */
        public function delete(string $filepath): self;

        /**
         * Move a file to a public directory. If the file is already in public
         * directory nothing happens
         * @param string $filepath Source file to move
         * @return string|null A path to a new location, or null otherwise
         */
        public function move(string $filepath): ?string;

        /**
         * Move a file to a protected directory. If the file is already in protected
         * directory nothing happens
         * @param string $filepath Source file to move
         * @return string|null A path to a new location, or null otherwise
         */
        public function moveProtect(string $filepath): ?string;

        /**
         * Move a file to a private directory. If the file is already in private
         * directory nothing happens
         * @param string $filepath Source file to move
         * @return string|null A path to a new location, or null otherwise
         */
        public function movePrivate(string $filepath): ?string;

        /**
         * Get a full path to a storage destination
         * @param string|null $path File or a directory
         * @return string Path to a storage destination (endpoint)
         */
        public function pathTo(?string $path = null): string;
    }

}
