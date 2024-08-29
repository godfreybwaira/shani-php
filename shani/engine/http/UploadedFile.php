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
        public static $storage;
        private \SplFileObject $stream;
        private ?string $destination = null;

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
         * Set a destination storage directory. If is null or not provided, then
         * the web root directory will be the last destination.
         * @param string $path Storage destination inside web storage directory.
         * It must have a leading / if provided.
         * @return self
         */
        public function setDestination(string $path): self
        {
            $this->destination = $path;
            return $this;
        }

        /**
         * Move a file from temporary directory to destination directory
         * @param App $app Application object
         * @param bool $protected If set to true, then the file will be uploaded to a protected storage
         * @param string $newName A new file name without extension
         * @return string File path to a new location
         * @throws \ErrorException Throw error if fails to create destination directory or not exists
         */
        public function save(App $app, bool $protected = true, string $newName = null): string
        {
            $destination = $app->storage()->pathTo($protected ? $app->config()->protectedStorage() : null) . $this->destination;
            $directory = self::createDirectory($destination . '/' . $this->file['type']);
            $filepath = $directory . '/' . ($newName ?? self::PREFIXES[rand(0, count(self::PREFIXES) - 1)]);
            $filepath .= hrtime()[1] . self::getExtension($this->file['name']);
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
            return substr($filepath, strlen($destination));
        }

        private static function getExtension(string $file): ?string
        {
            $dotPos = strrpos($file, '.');
            return $dotPos !== false ? substr($file, $dotPos) : null;
        }

        private static function createDirectory(string $destination): string
        {
            if (is_dir($destination) || mkdir($destination, Storage::FILE_MODE, true)) {
                return $destination;
            }
            throw new \ErrorException('Failed to create directory ' . $destination);
        }
    }

}
