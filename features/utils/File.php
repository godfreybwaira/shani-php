<?php

/**
 * Description of File
 * @author coder
 *
 * Created on: Aug 13, 2024 at 1:13:06 PM
 */

namespace features\utils {

    use features\exceptions\NotFoundException;
    use features\storage\LocalStorage;

    /**
     * Represents a file object with metadata and upload error handling.
     *
     * This class encapsulates information about a file, including:
     * - Path, name, type, size, and extension
     * - Any upload errors encountered
     *
     * It also implements JsonSerializable, allowing easy conversion
     * of file metadata into JSON format for APIs or logging.
     *
     * By default:
     * - Name → derived from basename of path if not provided
     * - Size → derived from filesystem stat if not provided
     * - Error → resolved into human-readable message if error code is given
     * - Extension → extracted from file name if present
     */
    final class File implements \JsonSerializable
    {

        /**
         * File modified time
         * @var int
         */
        public readonly int $modifiedTime;

        /**
         * File size in bytes.
         *
         * @var int
         */
        public readonly int $size;

        /**
         * File name.
         *
         * @var string
         */
        public readonly string $name;

        /**
         * File name without extension.
         *
         * @var string
         */
        public readonly string $shortName;

        /**
         * File MIME type.
         *
         * @var string
         */
        public readonly string $type;

        /**
         * Absolute file path on disk.
         *
         * @var string
         */
        public readonly string $path;

        /**
         * File upload error message (if any).
         *
         * @var string|null
         */
        public readonly ?string $error;

        /**
         * File extension (including dot), or null if none.
         *
         * @var string|null
         */
        public readonly ?string $extension;

        /**
         * Constructor for File.
         *
         * Initializes file metadata and resolves upload errors.
         *
         * @param string $path
         *     Full path to the file.
         *
         * @param string $type
         *     MIME type of the file.
         *
         * @param int|null $size
         *     File size in bytes. Defaults to filesystem stat size if null.
         *
         * @param string|null $name
         *     File name. Defaults to basename of path if null.
         *
         * @param int|null $error
         *     File upload error code. Defaults to null (no error).
         */
        public function __construct(
                string $path,
                ?string $type = null,
                ?int $size = null,
                ?string $name = null,
                ?int $error = null
        )
        {
            if (is_readable($path)) {
                $stat = stat($path);
                $this->path = $path;
                $this->name = $name ?? basename($path);
                $this->size = $size ?? $stat['size'];
                $this->modifiedTime = $stat['mtime'];
                $this->error = self::getFileErrors($error);
                $this->extension = self::getExtension($this->name);
                $this->shortName = $this->extension !== null ? substr($this->name, 0, strrpos($this->name, '.')) : $this->name;
                $this->type = $type ?? MediaType::fromExtension($this->extension);
            } else {
                throw new NotFoundException('File is not readable.');
            }
        }

        /**
         * Get file error (if any) as occurred during upload.
         *
         * @param int|null $error File error code.
         * @return string|null Human-readable error message or null if no error.
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
         * Extract file extension from file name.
         *
         * @param string $file File name.
         * @return string|null File extension (including dot) or null if none.
         */
        private static function getExtension(string $file): ?string
        {
            $dotPos = strrpos($file, '.');
            return $dotPos !== false ? substr($file, $dotPos) : null;
        }

        /**
         * Recursively copy a directory and all of its contents.
         *
         * This function will copy all files and subdirectories from the source
         * directory into the destination directory. If the destination does not
         * exist, it will be created automatically.
         *
         * @param string $source      Path to the source directory.
         * @param string $destination Path to the destination directory.
         *
         * @return bool Returns true on success, false if the source directory does not exist.
         */
        public static function copyDirectory(string $source, string $destination): bool
        {
            if (!is_dir($source)) {
                return false;
            }
            if (!is_dir($destination)) {
                mkdir($destination, LocalStorage::FILE_MODE, true);
            }
            $dir = opendir($source);
            while (($file = readdir($dir)) !== false) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $srcFile = $source . DIRECTORY_SEPARATOR . $file;
                $destFile = $destination . DIRECTORY_SEPARATOR . $file;
                if (is_dir($srcFile)) {
                    self::copyDirectory($srcFile, $destFile);
                } else {
                    copy($srcFile, $destFile);
                }
            }
            closedir($dir);
            return true;
        }

        /**
         * Serialize file metadata into JSON format.
         *
         * @return array<string, mixed> JSON representation of file metadata.
         */
        #[\Override]
        public function jsonSerialize(): array
        {
            return [
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
