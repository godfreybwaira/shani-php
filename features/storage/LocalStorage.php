<?php

/**
 * Description of LocalStorage
 * @author coder
 *
 * Created on: Feb 11, 2024 at 6:50:02 PM
 */

namespace features\storage {

    use features\exceptions\CustomException;
    use features\exceptions\ServerException;
    use features\storage\StorageMediaInterface;
    use features\utils\Concurrency;
    use features\utils\File;
    use features\utils\MediaType;
    use features\utils\URI;
    use shani\assets\StaticAssetServers;
    use shani\http\enums\HttpStatus;
    use shani\http\FileOutputStream;
    use shani\http\HttpCache;
    use shani\http\HttpHeader;
    use shani\launcher\App;
    use shani\launcher\Framework;
    use shani\presets\PathPresets;

    final class LocalStorage implements StorageMediaInterface
    {

        /**
         * Access to public assets in an asset directory. Everyone has an access.
         */
        private const ACCESS_ASSET = '/0';
        private const ID_SEPARATOR = '_', GID_INITIAL = 'g', PID_INITIAL = 'u';
        public const FILE_MODE = 0700;

        private readonly App $app;
        private readonly string $host, $storage;
        private readonly PathPresets $pathPreset;

        public function __construct(App $app)
        {
            $this->app = $app;
            $this->pathPreset = $app->config->pathPresets();
            $this->host = $app->request->uri->host();
            $this->storage = self::createShortcut($this->pathPreset->storage);
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
        public static function tryServe(App $app): bool
        {
            $assetServer = $app->config->getStaticAssetServer();
            if ($assetServer === StaticAssetServers::DISABLE) {
                $app->writer->send();
                return true;
            }
            $path = $app->request->uri->path();
            $prefix = self::getPrefix($path);
            switch ($prefix) {
                case self::ACCESS_ASSET:
                    $filepath = substr($path, strlen($prefix));
                    return self::sendFile($app, $assetServer, Framework::DIR_ASSETS . $filepath);
                case $app->config->pathPresets()->protectedStorage:
                    return self::serveProtected($app, $assetServer, $path);
                case $app->config->pathPresets()->publicStorage:
                    return self::sendFile($app, $assetServer, $app->storage()->pathTo($path));
                default:
                    return false;
            }
        }

        private static function serveProtected(App $app, StaticAssetServers $assetServer, string $filepath): bool
        {
            if (!$app->auth->attemptAuthentication()) {
                throw CustomException::forbidden($app);
            }
            $user = $app->auth->getUserDetails();
            $owners = self::getFileOwnership($filepath);
            if ($owners !== null && $owners['oid'] !== $user?->storageBucket) {
                if ($user?->groupStorageBucket !== $owners['gid']) {
                    throw CustomException::forbidden($app);
                }
            }
            return self::sendFile($app, $assetServer, $app->storage()->pathTo($filepath));
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

        private static function sendFile(App $app, StaticAssetServers $assetServer, string $filepath): bool
        {
            $path = $app->request->uri->path();
            $etag = md5($path);
            if ($app->request->header()->getOne(HttpHeader::IF_NONE_MATCH) === $etag) {
                $app->response->setStatus(HttpStatus::NOT_MODIFIED);
                $app->writer->send();
            } else {
                $cache = (new HttpCache())->setEtag($etag);
                $app->response->setStatus(HttpStatus::OK)->setCache($cache);
                match ($assetServer) {
                    StaticAssetServers::APACHE => self::delegateToApache($app, $filepath),
                    StaticAssetServers::NGINX => self::delegateToNginx($app, $path),
                    StaticAssetServers::SHANI => self::delegateToShani($app, $filepath),
                    default => $app->writer->send()
                };
            }
            return true;
        }

        /**
         * Get asset real path
         * @param string $path asset location relative to asset directory
         * @return string real path pointing to asset
         */
        public static function assetPath(string $path): string
        {
            return Framework::DIR_ASSETS . $path;
        }

        #[\Override]
        public function save(File $file, string $bucket = '/', bool $rename = true): string
        {
            $privateBucket = $this->app->auth->getUserDetails()?->storageBucket;
            if (empty($privateBucket)) {
                throw new ServerException('Client private Id cannot be empty');
            }
            $path = $this->pathTo($this->pathPreset->protectedStorage . $bucket);
            $prefix = self::PID_INITIAL . $privateBucket . self::ID_SEPARATOR;
            return $this->saveFile($file, $path, $prefix, $rename);
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
        public function assetUri(string $path): URI
        {
            return $this->uri(self::ACCESS_ASSET . $path);
        }

        #[\Override]
        public function uri(string $filepath): URI
        {
            return new URI($this->host . $filepath);
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
            if ($owners == null || $owners['oid'] === $this->app->auth->getUserDetails()?->storageBucket) {
                return file_exists($filepath) && unlink($filepath);
            }
            return false;
        }

        #[\Override]
        public function share2protected(string $filepath): ?string
        {
            $prefix = self::PID_INITIAL . $this->app->auth->getUserDetails()?->storageBucket;
            $prefix .= self::ID_SEPARATOR . self::GID_INITIAL;
            $bucket = $this->pathPreset->protectedStorage;
            return $this->shareFile($filepath, $bucket, $prefix);
        }

        #[\Override]
        public function share2group(string $filepath): ?string
        {
            $user = $this->app->auth->getUserDetails();
            $prefix = self::PID_INITIAL . $user?->storageBucket;
            $prefix .= self::ID_SEPARATOR . self::GID_INITIAL . $user?->groupStorageBucket;
            $bucket = $this->pathPreset->protectedStorage;
            return $this->shareFile($filepath, $bucket, $prefix);
        }

        #[\Override]
        public function share2other(string $filepath, string $otherId): ?string
        {
            $prefix = self::PID_INITIAL . $otherId;
            $bucket = $this->pathPreset->protectedStorage;
            return $this->shareFile($filepath, $bucket, $prefix);
        }

        #[\Override]
        public function share2public(string $filepath): ?string
        {
            $prefix = self::PID_INITIAL . $this->app->auth->getUserDetails()?->storageBucket;
            $bucket = $this->pathPreset->publicStorage;
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

        /**
         * Serve static assets using this framework
         * @param App $app Application object
         * @param string $filepath File path
         * @return void
         */
        private static function delegateToShani(App $app, string $filepath): void
        {
            $app->writer->send(new FileOutputStream($filepath));
        }

        /**
         * Serve static assets using this Nginx server
         * @param App $app Application object
         * @param string $filepath File path
         * @return void
         */
        public static function delegateToNginx(App $app, string $filepath): void
        {
            $app->response->header()->addAll([
                'X-Accel-Redirect' => $filepath,
                HttpHeader::CONTENT_TYPE => MediaType::fromFilename($filepath)
            ]);
            $app->writer->send();
        }

        /**
         * Serve static assets using Apache server
         * @param App $app Application object
         * @param string $filepath File path
         * @return void
         */
        public static function delegateToApache(App $app, string $filepath): void
        {
            $app->response->header()->addAll([
                'X-Sendfile' => $app->storage()->pathTo($filepath),
                HttpHeader::CONTENT_TYPE => MediaType::fromFilename($filepath)
            ]);
            $app->writer->send();
        }
    }

}