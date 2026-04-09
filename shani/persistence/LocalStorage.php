<?php

/**
 * Description of LocalStorage
 * @author coder
 *
 * Created on: Feb 11, 2024 at 6:50:02 PM
 */

namespace shani\persistence {

    use lib\Concurrency;
    use lib\File;
    use lib\http\FileOutput;
    use lib\http\HttpCache;
    use lib\http\HttpHeader;
    use lib\http\HttpStatus;
    use shani\contracts\StorageMedia;
    use shani\core\Framework;
    use shani\exceptions\CustomException;
    use shani\exceptions\ServerException;
    use shani\http\App;

    final class LocalStorage implements StorageMedia
    {

        /**
         * Access to public assets in an asset directory. Everyone has an access.
         */
        public const ACCESS_ASSET = '/0';
        public const FILE_MODE = 0700;
        private const ID_SEPARATOR = '_', GID_INITIAL = 'g', PID_INITIAL = 'u';

        private readonly App $app;
        private readonly string $host, $storage;

        public function __construct(App &$app)
        {
            $this->app = $app;
            $this->host = $app->request->uri->host();
            $this->storage = self::createShortcut($app->config->appStorage());
        }

        private static function createShortcut(string $target): string
        {
            $relativePart = substr($target, strlen(Framework::DIR_APPS));
            $shortcut = Framework::DIR_STORAGE . $relativePart;
            $isShortcut = is_link($shortcut);
            if ($isShortcut || file_exists($shortcut)) {
                if ($isShortcut && readlink($shortcut) === $target) {
                    return $shortcut;
                }
                !$isShortcut && is_dir($shortcut) ? rmdir($shortcut) : unlink($shortcut);
            }
            foreach ([$target, dirname($shortcut)] as $path) {
                if (!is_dir($path) && !mkdir($path, self::FILE_MODE, true)) {
                    throw new ServerException('Failed to create directory: ' . $path);
                }
            }
            if (symlink($target, $shortcut)) {
                return $shortcut;
            }
            throw new ServerException('Failed to create shortcut: ' . $shortcut);
        }

        private static function getPrefix(string $path): string
        {
            return substr($path, 0, strpos($path, '/', 1));
        }

        /**
         * Serve a static file e.g CSS, images and other static files.
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
                    return self::sendFile($app, Framework::DIR_ASSETS . $filepath);
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
            if (!$app->config->isAuthenticated()) {
                throw CustomException::forbidden($app);
            }
            $owners = self::getFileOwnership($filepath);
            if ($owners !== null && $owners['oid'] !== $app->config->getUserPrivateId()) {
                if (!$app->config->userGroupIdExists($owners['gid'])) {
                    throw CustomException::forbidden($app);
                }
            }
            return self::sendFile($app, $app->storage()->pathTo($filepath));
        }

        private static function getFileOwnership(string $filepath): ?array
        {
            $filename = basename($filepath);
            $ownership = substr($filename, 0, strrpos($filename, self::ID_SEPARATOR));
            if (empty($ownership)) {
                return null; //file has no owner
            }
            $owners = explode(self::ID_SEPARATOR, $ownership);
            return[
                'oid' => substr($owners[0], strlen(self::PID_INITIAL)),
                'gid' => !empty($owners[1]) ? substr($owners[1], strlen(self::GID_INITIAL)) : null,
            ];
        }

        private static function sendFile(App &$app, string $filepath): bool
        {
            $output = null;
            $etag = md5($app->request->uri->path());
            if ($app->request->header()->getOne(HttpHeader::IF_NONE_MATCH) === $etag) {
                $app->response->setStatus(HttpStatus::NOT_MODIFIED);
            } else {
                $cache = (new HttpCache())->setEtag($etag);
                $app->response->setStatus(HttpStatus::OK)->setCache($cache);
                $output = new FileOutput($filepath);
            }
            $app->writer->send($output);
            return true;
        }

        #[\Override]
        public function save(File $file, string $bucket = '/', bool $rename = true): string
        {
            $privateId = $this->app->config->getUserPrivateId();
            if (empty($privateId)) {
                throw new ServerException('Client private Id cannot be empty');
            }
            $path = $this->pathTo($this->app->config->appProtectedStorage() . $bucket);
            $prefix = self::PID_INITIAL . $privateId . self::ID_SEPARATOR;
            return $this->saveFile($file, $path, $prefix, $rename);
        }

        #[\Override]
        public function savePublic(File $file, string $bucket = '/', bool $rename = true): string
        {
            $path = $this->pathTo($this->app->config->appPublicStorage() . $bucket);
            return $this->saveFile($file, $path, null, $rename);
        }

        private function saveFile(File $file, string $path, ?string $prefix, bool $rename): string
        {
            $filename = $rename ? $prefix . substr(sha1(random_bytes(random_int(10, 70))), 0, rand(10, 15)) . $file->extension : $file->name;
            $directory = self::createDirectory($path . $file->type);
            $filepath = $directory . '/' . $filename;
            Concurrency::parallel(function ()use ($filepath, &$file) {
                $handle = fopen($filepath, 'a+b');
                $size = fstat($handle)['size'];
                if ($size < $file->size) {
                    fseek($handle, $size);
                    $stream = fopen($file->path, 'rb');
                    fseek($stream, $size);
                    $chunk = $size > 0 && $size <= Framework::BUFFER_SIZE ? $size : Framework::BUFFER_SIZE;
                    while (!feof($stream)) {
                        fwrite($handle, fread($stream, $chunk));
                    }
                    fclose($stream);
                }
                fclose($handle);
            });
            return substr($filepath, strlen($this->storage));
        }

        private static function createDirectory(string $destination): string
        {
            if (is_dir($destination) || mkdir($destination, self::FILE_MODE, true)) {
                return $destination;
            }
            throw new ServerException('Failed to create directory ' . $destination);
        }

        #[\Override]
        public function url(string $filepath): string
        {
            return $this->host . $filepath;
        }

        #[\Override]
        public function pathTo(?string $path = null): string
        {
            return $this->storage . $path;
        }

        #[\Override]
        public function delete(string $filepath): bool
        {
            $owners = self::getFileOwnership($filepath);
            if ($owners == null || $owners['oid'] === $this->app->config->getUserPrivateId()) {
                return file_exists($filepath) && unlink($filepath);
            }
            return false;
        }

        #[\Override]
        public function share2protected(string $filepath): ?string
        {
            $prefix = self::PID_INITIAL . $this->app->config->getUserPrivateId();
            $prefix .= self::ID_SEPARATOR . self::GID_INITIAL;
            $bucket = $this->app->config->appProtectedStorage();
            return $this->shareFile($filepath, $bucket, $prefix);
        }

        #[\Override]
        public function share2group(string $filepath, string $groupId): ?string
        {
            $prefix = self::PID_INITIAL . $this->app->config->getUserPrivateId();
            $prefix .= self::ID_SEPARATOR . self::GID_INITIAL . $groupId;
            $bucket = $this->app->config->appProtectedStorage();
            return $this->shareFile($filepath, $bucket, $prefix);
        }

        #[\Override]
        public function share2other(string $filepath, string $otherId): ?string
        {
            $prefix = self::PID_INITIAL . $otherId;
            $bucket = $this->app->config->appProtectedStorage();
            return $this->shareFile($filepath, $bucket, $prefix);
        }

        #[\Override]
        public function share2public(string $filepath): ?string
        {
            $prefix = self::PID_INITIAL . $this->app->config->getUserPrivateId();
            $bucket = $this->app->config->appPublicStorage();
            return $this->shareFile($filepath, $bucket, $prefix);
        }

        private function shareFile(string $filepath, string $bucket, string $prefix): ?string
        {
            $srcFile = $this->pathTo($filepath);
            if (is_readable($srcFile)) {
                $newName = $prefix . self::ID_SEPARATOR . self::getFilename($filepath);
                $srcBucket = self::getPrefix($filepath);
                $savepath = $bucket . substr(dirname($filepath), strlen($srcBucket));
                $destination = self::createDirectory($this->pathTo($savepath));
                if (!is_link($destination . '/' . $newName) && symlink($srcFile, $destination . '/' . $newName)) {
                    return $savepath . '/' . $newName;
                }
            }
            return null;
        }

        /**
         * Get a file name without client Id
         * @param string $file
         * @return string
         */
        private static function getFilename(string $file): string
        {
            $pos = strrpos($file, self::ID_SEPARATOR);
            if ($pos !== false) {
                return substr($file, $pos + strlen(self::ID_SEPARATOR));
            }
            return $file;
        }
    }

}