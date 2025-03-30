<?php

/**
 * Description of LocalStorage
 * @author coder
 *
 * Created on: Feb 11, 2024 at 6:50:02 PM
 */

namespace shani\persistence {

    use lib\http\HttpCache;
    use lib\http\HttpHeader;
    use lib\http\HttpStatus;
    use shani\contracts\StorageMedia;
    use shani\core\Definitions;
    use shani\http\App;
    use shani\http\UploadedFile;

    final class LocalStorage implements StorageMedia
    {

        /**
         * Access to public assets in an asset directory. Everyone has an access.
         */
        public const ACCESS_ASSET = '/0';

        private readonly App $app;
        private readonly string $host, $storage;

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
            $path = $app->request->uri->path;
            $prefix = substr($path, 0, strpos($path, '/', 1));
            switch ($prefix) {
                case self::ACCESS_ASSET:
                    $file = substr($path, strlen($prefix));
                    return self::sendFile($app, Definitions::DIR_ASSETS . $file);
                case $app->config->appProtectedStorage():
                    if (!$app->config->authenticated) {
                        throw HttpStatus::forbidden($app);
                    }
                case $app->config->appPublicStorage():
                    return self::sendFile($app, $app->storage()->pathTo($path));
                default:
                    return false;
            }
        }

        private static function sendFile(App &$app, string $file): bool
        {
            $etag = md5($app->request->uri->path);
            if ($app->request->header()->get(HttpHeader::IF_NONE_MATCH) === $etag) {
                $app->response->setStatus(HttpStatus::NOT_MODIFIED);
                $app->send();
            } else {
                $cache = (new HttpCache())->setEtag($etag);
                $app->response->setStatus(HttpStatus::OK)->setCache($cache);
                $app->stream($file);
            }
            return true;
        }

        public function save(UploadedFile $file, string $bucket = null): ?string
        {
            return self::persist($file, $this->app->config->appPublicStorage() . $bucket);
        }

        public function saveProtect(UploadedFile $file, string $bucket = null): ?string
        {
            return self::persist($file, $this->app->config->appProtectedStorage() . $bucket);
        }

        private static function persist(UploadedFile &$file, string $savePath): ?string
        {
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
            return $this->host . $filepath;
        }

        /**
         * Get a full path to a file or directory
         * @param string|null $path File or directory
         * @return string Path to a file or directory
         */
        private function pathTo(?string $path = null): string
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