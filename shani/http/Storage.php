<?php

/**
 * Description of Storage
 * @author coder
 *
 * Created on: Aug 14, 2024 at 12:48:49 PM
 */

namespace shani\http {

    use shani\core\Definitions;

    final class Storage
    {

        private readonly App $app;
        private readonly string $root;

        public const FILE_MODE = 0750;

        /**
         * Create a storage area for files and directories
         * @param App $app
         */
        public function __construct(App &$app)
        {
            $this->app = $app;
            $this->root = Definitions::DIR_APPS . $app->config->staticAssetStorage();
        }

        /**
         * Get a file from application web storage directory
         * @param string|null $path A file path relative to web root directory
         * @return string
         */
        public function pathTo(?string $path = null): string
        {
            return $this->root . $path;
        }

        /**
         * Delete a file
         * @param string|null $path file to delete
         * @return bool True on success, false otherwise
         */
        public function delete(?string $path): bool
        {
            $filepath = $this->pathTo($path);
            if (!is_file($filepath)) {
                return false;
            }
            return unlink($filepath);
        }

        private function getHash(?string $path, string $algorithm = 'sha256'): ?string
        {
            $filepath = $this->pathTo($path);
            if ($path === null || !is_file($filepath)) {
                return $path;
            }
            return hash_file($algorithm, $filepath);
        }

        private function getPath(?string $path): ?string
        {
            if ($path === null || is_file($path)) {
                return $path;
            }
            $filepath = $this->pathTo($path);
            if (is_file($filepath)) {
                return $filepath;
            }
            return null;
        }

        /**
         * Create and return a file info object
         * @param string $path a file path
         * @return \SplFileInfo
         * @throws \ErrorException
         */
        public function getInfo(string $path): \SplFileInfo
        {
            $filepath = $this->pathTo($path);
            if (is_file($filepath)) {
                return new \SplFileInfo($filepath);
            }
            throw new \ErrorException('File not exists');
        }

        /**
         * Move a file from where it is to a destination directory within a web storage
         * @param string $srcFile Path to a source file to move
         * @param string $destinationFolder Destination directory relative to web storage directory
         * @return string|null Path to a new file location, or null if it fails.
         */
        public function moveTo(string $srcFile, string $destinationFolder): ?string
        {
            return $this->transferFile($srcFile, $destinationFolder, fn($src, $dst) => rename($src, $dst));
        }

        /**
         * Copy a file from where it is to a destination directory within a web storage
         * @param string $srcFile Path to a source file to copy
         * @param string $destinationFolder Destination directory relative to web storage directory
         * @return string|null Path to a new file location, or null if it fails.
         */
        public function copyTo(string $srcFile, string $destinationFolder): ?string
        {
            return $this->transferFile($srcFile, $destinationFolder, fn($src, $dst) => copy($src, $dst));
        }

        private function transferFile(string $src, string $dst, callable $cb): ?string
        {
            $filepath = $this->getPath($src);
            if ($filepath === null) {
                return null;
            }
            $filename = '/' . basename($filepath);
            $destiny = $this->pathTo($dst);
            if (is_dir($destiny) || mkdir($destiny, self::FILE_MODE, true)) {
                if ($cb($filepath, $destiny . $filename)) {
                    return $dst . $filename;
                }
            }
            return null;
        }

        /**
         * Get a full qualified URL to a web root directory (web storage)
         * @param string $path Path to a file in a web root directory
         * @param bool $protected If set to true, then the file will be loaded from a protected storage
         * @return string URL referring to a file from storage
         */
        public function urlTo(string $path, bool $protected = true): string
        {
            if ($protected) {
                $storage = $this->app->config->protectedStorage();
                $url = $this->app->request->uri->host() . self::PRIVATE_PREFIX;
                return $url . (empty($storage) ? $path : substr($path, strlen($storage)));
            }
            return $this->app->request->uri->host() . self::STORAGE_PREFIX . $path;
        }

        /**
         * Zip and compress a file
         * @param string|array $sourcePath A file to compress
         * @return string|null Path to a zipped file
         */
        public function zip(string|array $sourcePath): ?string
        {
            $zip = new \ZipArchive();
            $archive = stream_get_meta_data(tmpfile())['uri'];
            $zip->open($archive, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            if (is_array($sourcePath)) {
                foreach ($sourcePath as $path) {
                    $filepath = $this->getPath($path);
                    if ($filepath !== null) {
                        $zip->addFile($filepath, basename($filepath));
                    }
                }
            } else {
                $filepath = $this->getPath($sourcePath);
                if ($filepath !== null) {
                    $zip->addFile($filepath, basename($filepath));
                }
            }
            $zip->close();
            return $archive;
        }
    }

}
