<?php

/**
 * Description of FileOutputStream
 * @author goddy
 *
 * Created on: Oct 18, 2025 at 11:45:58 AM
 */

namespace shani\http {

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
         * Name of the file to be downloaded.
         * If not null, a download will be initiated on the client application.
         *
         * @var string|null
         */
        public readonly ?string $filename;

        /**
         * Absolute path to an existing file.
         *
         * @var string
         */
        public readonly string $filepath;

        /**
         * Constructs a new FileOutputStream instance.
         *
         * @param string      $filepath  Absolute path to an existing file.
         * @param string|null $filename  Optional name of the file. If provided,
         *                               the client will initiate a download.
         * @param int|null    $chunkSize Size in bytes to send/stream the file.
         *                               Defaults to Framework::BUFFER_SIZE.
         */
        public function __construct(string $filepath, string $filename = null, int $chunkSize = null)
        {
            $this->filepath = $filepath;
            $this->filename = $filename;
            $this->chunkSize = $chunkSize ?? Framework::BUFFER_SIZE;
        }
    }

}
