<?php

/**
 * Description of StorageMedia
 * @author coder
 *
 * Created on: Mar 27, 2025 at 5:29:05 PM
 */

namespace shani\contracts {

    use shani\http\UploadedFile;

    interface StorageMedia
    {

        /**
         * Save a file to a public storage media. Public media is accessible
         * by everyone
         * @param UploadedFile $file A file object to save
         * @return string|null Path to a saved file, null otherwise
         */
        public function save(UploadedFile $file): ?string;

        /**
         * Save a file to a protected storage media. Protected media is accessible
         * by authenticated clients only
         * @param UploadedFile $file A file object to save
         * @return string|null Path to a saved file, null otherwise
         */
        public function saveProtect(UploadedFile $file): ?string;

        /**
         * Save a file to a protected storage media. This resource is only accessible
         * to authenticated clients with the same group Id
         * @param UploadedFile $file A file object to save
         * @return string|null Path to a saved file, null otherwise
         * @see \shani\advisors\Configuration::clientGroupId()
         */
        public function savePrivate(UploadedFile $file): ?string;

        /**
         * Get file URL
         * @param string $file Saved file path
         * @return string URL referring to a file from a storage media
         */
        public function url(string $file): string;

        /**
         * Download a file
         * @param string $file Saved file path
         * @param string|null $filename Optional new file name
         * @return self
         */
        public function download(string $file, ?string $filename = null): self;

        /**
         * Delete a file
         * @param string $file Saved file path
         * @return self
         */
        public function delete(string $file): self;

        /**
         * Move a file to a destination bucket
         * @param string $file Source file
         * @param string $destination Destination directory
         * @return string
         */
        public function moveTo(string $file, string $destination): ?string;
    }

}
