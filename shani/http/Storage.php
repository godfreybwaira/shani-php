<?php

/**
 * Description of Asset
 * @author coder
 *
 * Created on: Feb 11, 2024 at 6:50:02 PM
 */

namespace shani\http {

    use library\http\HttpCache;
    use library\http\HttpHeader;
    use library\http\HttpStatus;
    use shani\core\Definitions;

    final class Storage
    {

        private readonly App $app;
        private readonly string $host;
        public readonly string $path;

        private const ASSET_PREFIX = '/0';
        private const STORAGE_PREFIX = '/1';
        public const FILE_MODE = 0750;

        public function __construct(App &$app)
        {
            $this->app = $app;
            $this->host = $app->request->uri->host();
            $this->path = $app->config->appStorage();
        }

        /**
         * Serve static content e.g CSS, images and other static files.
         * @param App $app Application object
         * @return bool True on success, false otherwise.
         */
        public static function tryServe(App &$app): bool
        {
            $prefix = substr($app->request->uri->path, 0, strpos($app->request->uri->path, '/', 1));
            return match ($prefix) {
                self::ASSET_PREFIX => self::sendFile($app, Definitions::DIR_ASSETS, $prefix),
                self::STORAGE_PREFIX => self::sendFile($app, $app->storage()->pathTo(), $prefix),
                default => false
            };
        }

        private static function sendFile(App &$app, string $rootPath, string $prefix): bool
        {
            $etag = md5($app->request->uri->path);
            if ($app->request->header()->get(HttpHeader::IF_NONE_MATCH) === $etag) {
                $app->response->setStatus(HttpStatus::NOT_MODIFIED);
                $app->send();
            } else {
                $file = $rootPath . substr($app->request->uri->path, strlen($prefix));
                $cache = (new HttpCache())->setEtag($etag);
                $app->response->setStatus(HttpStatus::OK)->setCache($cache);
                $app->stream($file);
            }
            return true;
        }

        /**
         * Get absolute path of a file from static asset directory
         * @param string $path A file path
         * @return string
         */
        public static function assetPath(string $path): string
        {
            return Definitions::DIR_ASSETS . $path;
        }

        /**
         * Get URL to a static asset resource
         * @param string $path Path to a file relative to static asset directory
         * @return string
         */
        public function assetUrl(string $path): string
        {
            return $this->host . self::ASSET_PREFIX . $path;
        }

        /**
         * Get file URL
         * @param string $path Path to a file relative to a web root directory
         * @return string URL referring to a file from storage
         */
        public function url(string $path): string
        {
            return $this->host . self::STORAGE_PREFIX . $path;
        }

        public function pathTo(?string $path = null): string
        {
            return $this->path . $path;
        }

        /**
         * Delete a file
         * @param string $path file to delete
         * @return bool True on success, false otherwise
         */
        public function delete(string $path): bool
        {
            $filepath = $this->pathTo($path);
            if (is_file($filepath)) {
                return unlink($filepath);
            }
            return false;
        }

        /**
         * Get hash of a file
         * @param string $path Path to a file
         * @param string $algorithm Hashing algorithms
         * @return string|null Returns hash of a file, or null if file does not exists.
         */
        private function getHash(string $path, string $algorithm = 'sha256'): ?string
        {
            $filepath = $this->pathTo($path);
            if (is_file($filepath)) {
                return hash_file($algorithm, $filepath);
            }
            return null;
        }

        /**
         * Get information about the file
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
            $filepath = $this->pathTo($src);
            if (!is_file($filepath)) {
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
         * Zip and compress a file
         * @param string|array $path A file(s) to add to zip archive
         * @return string|null Path to a zipped file
         */
        public function zip(string|array $path): ?string
        {
            $zip = new \ZipArchive();
            $archive = stream_get_meta_data(tmpfile())['uri'];
            $zip->open($archive, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
            if (is_array($path)) {
                foreach ($path as $file) {
                    $filepath = $this->pathTo($file);
                    if (is_file($filepath)) {
                        $zip->addFile($filepath, basename($filepath));
                    }
                }
            } else {
                $filepath = $this->pathTo($path);
                if (is_file($filepath)) {
                    $zip->addFile($filepath, basename($filepath));
                }
            }
            $zip->close();
            return $archive;
        }
    }

}