<?php

/**
 * Description of UploadedFile
 * @author coder
 *
 * Created on: Aug 13, 2024 at 1:13:06 PM
 */

namespace shani\engine\http {

    use shani\advisors\Configuration;
    use shani\engine\core\Definitions;

    final class UploadedFile
    {

        private const PREFIXES = ['y', 'e', 's', 'u'];

        public readonly string $name, $type;
        public \SplFileObject $stream;
        public readonly ?string $error;
        public readonly int $size;
        private ?string $savedPath;

        public function __construct(string $path, string $type, ?int $size = null, ?string $name = null, ?int $error = null)
        {
            $this->type = $type;
            $this->savedPath = null;
            $this->name = $name ?? basename($path);
            $this->size = $size ?? stat($path)['size'];
            $this->error = self::getFileErrors($error);
            $this->stream = new \SplFileObject($path, 'rb');
        }

        public function __destruct()
        {
            unset($this->stream);
        }

        /**
         * Get path to a saved file relative to a storage directory
         * @return string|null Path to a saved file, null if the file is not yet saved.
         */
        public function savedPath(): ?string
        {
            return $this->savedPath;
        }

        /**
         * Get file error (if any) as occurred during upload
         * @param int|null $error File error
         * @return string|null Array of error code as key and error message or null if no error
         */
        private static function getFileErrors(?int $error): ?string
        {
            if ($error === null) {
                return null;
            }
            return match ($error) {
                UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'File is too large.',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
                UPLOAD_ERR_OK => null,
                default => 'Unknown error.',
            };
        }

        /**
         * Move a file from temporary directory to destination directory
         * @param string $destination File storage destination
         * @param string $newName A new file name without extension
         * @return self
         * @throws \ErrorException Throw error if fails to create destination directory or not exists
         * @see Configuration::protectedStorage()
         */
        public function save(App &$app, string $destination = null, string $newName = null): self
        {
            $savePath = $app->storage()->pathTo($destination);
            $directory = self::createDirectory($savePath . '/' . $this->type);
            $filepath = $directory . '/' . ($newName ?? self::PREFIXES[rand(0, count(self::PREFIXES) - 1)]);
            $filepath .= hrtime()[1] . self::getExtension($this->name);
            $file = fopen($filepath, 'a+b');
            $size = fstat($file)['size'];
            if ($size < $this->size) {
                fseek($file, $size);
                $this->stream->seek($size);
                $chunk = $size > 0 && $size <= Definitions::BUFFER_SIZE ? $size : Definitions::BUFFER_SIZE;
                while ($this->stream->valid()) {
                    fwrite($file, $this->stream->fread($chunk));
                }
                unlink($this->stream->getPathname());
            }
            fclose($file);
            $this->savedPath = substr($filepath, strlen($savePath));
            return $this;
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
