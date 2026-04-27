<?php

/**
 * Description of FileOutputStream
 * @author goddy
 *
 * Created on: Oct 18, 2025 at 11:45:58 AM
 */

namespace shani\http {

    use features\utils\File;
    use features\utils\MediaType;
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
        public readonly ?string $name;

        /**
         * File MIME type.
         *
         * @var string
         */
        public readonly string $type;

        /**
         * Absolute path to an existing file.
         *
         * @var string
         */
        public readonly string $path;

        /**
         * Constructs a new FileOutputStream instance.
         *
         * @param string        $path  Absolute path to an existing file.
         * @param string|null   $type  File MIME type.
         * @param string|null   $name  Optional name of the file. If provided,
         *                             the client will initiate a download.
         * @param int|null      $chunkSize  Size in bytes to send/stream the file.
         *                               Defaults to Framework::BUFFER_SIZE.
         */
        public function __construct(string $path, string $type = null, string $name = null, int $chunkSize = null)
        {
            $this->path = $path;
            $this->name = $name;
            $this->type = $type ?? MediaType::fromFilename($path);
            $this->chunkSize = $chunkSize ?? Framework::BUFFER_SIZE;
        }

        /**
         * Create a FileOutputStream object from File object
         * @param File $file File Object
         * @return FileOutputStream
         */
        public static function fromFile(File $file): FileOutputStream
        {
            return new self($file->path, $file->type, $file->name);
        }
    }

}
