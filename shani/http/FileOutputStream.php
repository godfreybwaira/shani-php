<?php

/**
 * Description of FileOutputStream
 * @author goddy
 *
 * Created on: Oct 18, 2025 at 11:45:58 AM
 */

namespace shani\http {

    use features\utils\File;
    use shani\launcher\Framework;

    /**
     * Represents a file output stream for sending or downloading files
     * in chunks to the client application.
     */
    final class FileOutputStream
    {

        /**
         * Size in bytes to send/stream a file.
         * Defaults to Framework::BUFFER_SIZE if not specified.
         *
         * @var int
         */
        public readonly int $chunkSize;

        /**
         * Whether to download a file to stream it.
         *
         * @var string|null
         */
        public readonly bool $downloadable;

        /**
         * File object to send as output
         * @var File
         */
        public readonly File $file;

        /**
         * Constructs a new FileOutputStream instance.
         *
         * @param File      $file           Absolute path to an existing file.
         * @param bool      $downloadable   Whether to download a file to stream it.
         * @param int|null  $chunkSize      Size in bytes to send/stream the file.
         *                                  Defaults to Framework::BUFFER_SIZE.
         */
        public function __construct(File $file, bool $downloadable = false, int $chunkSize = null)
        {
            $this->file = $file;
            $this->downloadable = $downloadable;
            $this->chunkSize = $chunkSize ?? Framework::BUFFER_SIZE;
        }
    }

}
