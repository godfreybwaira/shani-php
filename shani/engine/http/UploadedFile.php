<?php

/**
 * Description of UploadedFile
 * @author coder
 *
 * Created on: Aug 13, 2024 at 1:13:06 PM
 */

namespace shani\engine\http {

    use shani\engine\core\Definitions;

    final class UploadedFile
    {

        private array $file;
        private \SplFileObject $stream;
        public static $storage;

        private const PREFIXES = ['y', 'e', 's', 'u'];

        public function __construct(array &$file)
        {
            $this->file = $file;
            $this->stream = new \SplFileObject($file['tmp_name'], 'rb');
        }

        public function __destruct()
        {
            unset($this->stream);
        }

        /**
         * Get file information as array
         * @return array
         */
        public function toArray(): array
        {
            return $this->file;
        }

        /**
         * Get file MIME type
         * @return string
         */
        public function getType(): string
        {
            return $this->file['type'];
        }

        /**
         * Get file size
         * @return int File size
         */
        public function getSize(): int
        {
            return $this->file['size'];
        }

        /**
         * Get file error (if any) as occurred during upload
         * @return array|null Array of error code as key and error message or null if no error
         */
        public function getError(): ?array
        {
            if ($this->file['error'] === UPLOAD_ERR_OK) {
                return null;
            }
            $msg = match ($this->file['error']) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File is too large.',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
                default => 'Unknown error.',
            };
            return [$this->file['error'] => $msg];
        }

        /**
         * Get file name as provided by client
         * @return string File name
         */
        public function getName(): string
        {
            return $this->file['name'];
        }

        /**
         * Get file object as stream
         * @return \SplFileObject File object
         */
        public function getStream(): \SplFileObject
        {
            return $this->stream;
        }

        /**
         * Move a file from temporary directory to destination directory
         * @param string|null $location A location to save a file, relative to web
         * root directory. If set, then it must have a leading slash
         * @param string $newName A new file name without extension
         * @return string File path to a new location
         * @throws \ErrorException Throw error if fails to create destination directory or not exists
         */
        public function save(string $location = null, string $newName = null): string
        {

            $directory = self::createDirectory($location . '/' . $this->file['type']);
            $filepath = $directory . '/' . ($newName ?? self::PREFIXES[rand(0, count(self::PREFIXES) - 1)]);
            $filepath .= hrtime(true) . self::getExtension($this->file['name']);
            $file = fopen($filepath, 'a+b');
            $size = fstat($file)['size'];
            if ($size < $this->file['size']) {
                fseek($file, $size);
                $this->stream->seek($size);
                $chunk = $size > 0 && $size <= Definitions::BUFFER_SIZE ? $size : Definitions::BUFFER_SIZE;
                while ($this->stream->valid()) {
                    fwrite($file, $this->stream->fread($chunk));
                }
                unlink($this->file['tmp_name']);
            }
            fclose($file);
            return substr($filepath, strlen(self::$storage));
        }

        private static function getExtension(string $file): ?string
        {
            $dotPos = strrpos($file, '.');
            return $dotPos !== false ? substr($file, $dotPos) : null;
        }

        private static function createDirectory(string $destination): string
        {
            $directory = self::$storage . $destination;
            $created = is_dir($directory) || mkdir($directory, Disk::FILE_MODE, true);
            if (!$created) {
                throw new \ErrorException('Failed to create directory ' . $directory);
            }
            return $directory;
        }

        public static function setDefaultStorage(string $path): void
        {
            self::$storage = $path;
        }
    }

}
