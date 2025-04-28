<?php

/**
 * Description of LocalStorage
 * @author coder
 *
 * Created on: Feb 11, 2024 at 6:50:02 PM
 */

namespace shani\persistence {

    use lib\File;
    use lib\http\HttpCache;
    use lib\http\HttpHeader;
    use lib\http\HttpStatus;
    use shani\contracts\StorageMedia;
    use shani\core\Definitions;
    use shani\exceptions\CustomException;
    use shani\exceptions\ServerException;
    use shani\http\App;

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
            $this->storage = self::createPath($app->config->root(), $app->config->appStorage());
        }

        private static function createPath(string $root, string $target): string
        {
            $pos = strpos($root, '/', 1);
            $dirname = $pos === false ? $root : substr($root, 0, $pos);
            $path = Definitions::DIR_STORAGE . $dirname;
            if (is_link($path) || symlink($target, $path)) {
                return $path;
            }
            throw new ServerException('Failed to create directory ' . $path);
        }

        private static function getPrefix(string $path): string
        {
            return substr($path, 0, strpos($path, '/', 1));
        }

        /**
         * Serve static content e.g CSS, images and other static files.
         * @param App $app Application object
         * @return bool True on success, false otherwise.
         */
        public static function tryServe(App &$app): bool
        {
            $path = $app->request->uri->path();
            $prefix = self::getPrefix($path);
            switch ($prefix) {
                case self::ACCESS_ASSET:
                    $filepath = substr($path, strlen($prefix));
                    return self::sendFile($app, Definitions::DIR_ASSETS . $filepath);
                case $app->config->appProtectedStorage():
                    return self::serveProtected($app, $path);
                case $app->config->appPublicStorage():
                    return self::sendFile($app, $app->storage()->pathTo($path));
                default:
                    return false;
            }
        }

        private static function serveProtected(App &$app, string $filepath): bool
        {
            if (!$app->config->authenticated) {
                throw CustomException::forbidden($app);
            }
            $filename = basename($filepath);
            $groupId = substr($filename, 0, strrpos($filename, '-'));
            if (!empty($groupId) && $groupId !== $app->config->clientGroupId()) {
                throw CustomException::forbidden($app);
            }
            return self::sendFile($app, $app->storage()->pathTo($filepath));
        }

        private static function sendFile(App &$app, string $filepath): bool
        {
            $etag = md5($app->request->uri->path());
            if ($app->request->header()->getOne(HttpHeader::IF_NONE_MATCH) === $etag) {
                $app->response->setStatus(HttpStatus::NOT_MODIFIED);
                $app->send();
            } else {
                $cache = (new HttpCache())->setEtag($etag);
                $app->response->setStatus(HttpStatus::OK)->setCache($cache);
                $app->stream($filepath);
            }
            return true;
        }

        public function save(File $file, string $bucket = '/'): ?string
        {
            $path = $this->pathTo($this->app->config->appPublicStorage() . $bucket);
            return self::persist($file, $this->storage, $path);
        }

        public function save2protect(File $file, string $bucket = '/'): ?string
        {
            $path = $this->pathTo($this->app->config->appProtectedStorage() . $bucket);
            return self::persist($file, $this->storage, $path);
        }

        public function save2private(File $file, string $bucket = '/'): ?string
        {
            $path = $this->pathTo($this->app->config->appProtectedStorage() . $bucket);
            $groupId = $this->app->config->clientGroupId();
            if (empty($groupId)) {
                throw new ServerException('Client group Id cannot be empty');
            }
            return self::persist($file, $this->storage, $path, $groupId . '-');
        }

        private static function persist(File &$file, string $root, string $savePath, string $prefix = null): ?string
        {
            $filename = $prefix . substr(md5(random_bytes(random_int(10, 70))), 0, 20);
            $directory = self::createDirectory($savePath . $file->type);
            $filepath = $directory . '/' . $filename . $file->extension;
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
            return substr($filepath, strlen($root));
        }

        private static function createDirectory(string $destination): string
        {
            if (is_dir($destination) || mkdir($destination, self::FILE_MODE, true)) {
                return $destination;
            }
            throw new ServerException('Failed to create directory ' . $destination);
        }

        public function url(string $filepath): string
        {
            return $this->host . $filepath;
        }

        public function pathTo(?string $path = null): string
        {
            return $this->storage . $path;
        }

        public function download(string $filepath, ?string $filename = null): self
        {
            $disposition = 'attachment; filename="' . ($filename ?? basename($filepath)) . '"';
            $this->app->response->header()->addOne(HttpHeader::CONTENT_DISPOSITION, $disposition);
            return $this->app->stream($filepath);
        }

        public function delete(string $filepath): self
        {
            $path = $this->pathTo($filepath);
            if (is_file($path)) {
                unlink($path);
            }
            return $this;
        }

        public function move2protect(string $filepath): ?string
        {
            $bucket = $this->app->config->appProtectedStorage();
            return $this->moveFile($filepath, $bucket);
        }

        public function move2private(string $filepath): ?string
        {
            $bucket = $this->app->config->appProtectedStorage();
            $groupId = $this->app->config->clientGroupId();
            if (empty($groupId)) {
                throw new ServerException('Client group Id cannot be empty');
            }
            return $this->moveFile($filepath, $bucket, $groupId . '-');
        }

        public function move(string $filepath): ?string
        {
            $bucket = $this->app->config->appPublicStorage();
            return $this->moveFile($filepath, $bucket);
        }

        private function moveFile(string $filepath, string $bucket, string $groupId = null): ?string
        {
            $prefix = self::getPrefix($filepath);
            if ($prefix !== $bucket && is_file($filepath)) {
                //= /prefix/path/to/file.txt
                $shortPath = substr($filepath, strlen($prefix));
                //= /path/to/file.txt
                $folder = $bucket . dirname($shortPath);
                $filename = $groupId . self::getFilename(basename($shortPath));
                $destination = self::createDirectory($this->pathTo($folder));
                if (rename($filepath, $destination . '/' . $filename)) {
                    return $folder . '/' . $filename;
                }
            }
            return null;
        }

        /**
         * Get a file name without client group Id
         * @param string $file
         * @return string
         */
        private static function getFilename(string $file): string
        {
            $pos = strrpos($file, '-');
            if ($pos !== false) {
                return substr($file, $pos + 1);
            }
            return $file;
        }
    }

}