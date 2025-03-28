<?php

/**
 * Description of LocalStorage
 * @author coder
 *
 * Created on: Feb 11, 2024 at 6:50:02 PM
 */

namespace shani\http {

    use lib\http\HttpCache;
    use lib\http\HttpHeader;
    use lib\http\HttpStatus;
    use shani\core\Definitions;
    use shani\contracts\StorageMedia;

    final class LocalStorage implements StorageMedia
    {

        private readonly App $app;
        private readonly string $host, $storage;

        private const ASSET_PREFIX = '/0';
        private const STORAGE_PREFIX = '/1';
        private const FILE_MODE = 0700;

        public function __construct(App &$app)
        {
            $this->app = $app;
            $this->host = $app->request->uri->host();
            $this->storage = self::createPath($app->config->appStorage());
        }

        private static function createPath(string $target): string
        {
            $path = Definitions::DIR_ASSETS . '/' . basename($target);
            if (is_dir($path) || symlink($target, $path)) {
                return $path;
            }
            throw new \Exception('Failed to create directory ' . $path);
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

        public function save(UploadedFile $file, string $bucket = null): ?string
        {
            $savePath = $this->pathTo($bucket);
            $directory = self::createDirectory($savePath . '/' . $file->type);
            $filepath = $directory . '/' . md5(random_bytes(random_int(10, 70))) . $file->extension;
            $handle = fopen($filepath, 'a+b');
            $size = fstat($handle)['size'];
            if ($size < $file->size) {
                fseek($handle, $size);
                $stream = fopen($file->path, 'rb');
                fseek($stream, $size);
                $chunk = $size > 0 && $size <= Definitions::BUFFER_SIZE ? $size : Definitions::BUFFER_SIZE;
                while (!feof($stream)) {
                    fwrite($handle, fread($stream, $chunk));
                }
                fclose($stream);
            }
            fclose($handle);
            return substr($filepath, strlen($savePath));
        }

        private static function createDirectory(string $destination): string
        {
            if (is_dir($destination) || mkdir($destination, self::FILE_MODE, true)) {
                return $destination;
            }
            throw new \Exception('Failed to create directory ' . $destination);
        }

        public function url(string $filepath): string
        {
            return $this->host . self::STORAGE_PREFIX . $filepath;
        }

        /**
         * Get a full path to a file or directory
         * @param string|null $path File or directory
         * @return string Path to a file or directory
         */
        public function pathTo(?string $path = null): string
        {
            return $this->storage . $path;
        }

        public function download(string $file, ?string $filename = null): self
        {
            $disposition = 'attachment; filename="' . ($filename ?? basename($file)) . '"';
            $this->app->response->header()->add(HttpHeader::CONTENT_DISPOSITION, $disposition);
            return $this->app->stream($file);
        }

        public function delete(string $file): self
        {
            $path = $this->pathTo($file);
            if (is_file($path)) {
                unlink($path);
            }
            return $this;
        }

        public function moveTo(string $file, string $bucket): ?string
        {
            $filepath = $this->pathTo($file);
            if (is_file($filepath)) {
                $filename = '/' . basename($filepath);
                $destination = self::createDirectory($this->pathTo($bucket));
                if (rename($filepath, $destination . $filename)) {
                    return $bucket . $filename;
                }
            }
            return null;
        }
    }

}