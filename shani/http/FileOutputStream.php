<?php

/**
 * Description of FileOutputStream
 * @author goddy
 *
 * Created on: Oct 18, 2025 at 11:45:58 AM
 */

namespace shani\http {

    use shani\launcher\Framework;

    final class FileOutputStream
    {

        /**
         *  Size in bytes to send/stream a file. Default value is Framework::BUFFER_SIZE
         * @var int
         */
        public readonly int $chunkSize;

        /**
         * Name of a file (if not null then download will be initiated on client application)
         * @var string|null
         */
        public readonly ?string $filename;

        /**
         * Absolute path to an existing file.
         * @var string
         */
        public readonly string $filepath;

        /**
         * Represent the file to be streamed or downloaded (if $filename is given)
         * @param string $filepath Absolute path to an existing file.
         * @param string $filename Name of a file (if not null then download will be initiated on client application)
         * @param int $chunkSize Size in bytes to send/stream a file. Default value is Framework::BUFFER_SIZE
         */
        public function __construct(string $filepath, string $filename = null, int $chunkSize = null)
        {
            $this->filepath = $filepath;
            $this->filename = $filename;
            $this->chunkSize = $chunkSize ?? Framework::BUFFER_SIZE;
        }
    }

}
