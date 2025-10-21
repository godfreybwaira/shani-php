<?php

/**
 * Description of File
 * @author coder
 *
 * Created on: Aug 13, 2024 at 1:13:06 PM
 */

namespace lib {

    final class File implements \JsonSerializable
    {

        public readonly int $size;
        public readonly string $name, $type, $path;
        public readonly ?string $error, $extension;

        public function __construct(string $path, string $type, ?int $size = null, ?string $name = null, ?int $error = null)
        {
            $this->type = $type;
            $this->path = $path;
            $this->name = $name ?? basename($path);
            $this->size = $size ?? stat($path)['size'];
            $this->error = self::getFileErrors($error);
            $this->extension = self::getExtension($this->name);
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

        private static function getExtension(string $file): ?string
        {
            $dotPos = strrpos($file, '.');
            return $dotPos !== false ? substr($file, $dotPos) : null;
        }

        #[\Override]
        public function jsonSerialize(): array
        {
            return[
                'name' => $this->name,
                'type' => $this->type,
                'size' => $this->size,
                'extension' => $this->extension,
                'path' => $this->path,
                'error' => $this->error
            ];
        }
    }

}
