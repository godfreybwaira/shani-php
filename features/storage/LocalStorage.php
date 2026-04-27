<?php

/**
 * Description of LocalStorage
 * @author coder
 *
 * Created on: Feb 11, 2024 at 6:50:02 PM
 */

namespace features\storage {

    use features\authentication\UserDetailsDto;
    use features\exceptions\ServerException;
    use features\storage\StorageMediaInterface;
    use features\utils\Concurrency;
    use features\utils\File;
    use features\utils\URI;
    use features\assets\StaticAssetOwnership;
    use shani\config\PathConfig;
    use shani\launcher\App;
    use shani\launcher\Framework;

    final class LocalStorage implements StorageMediaInterface
    {

        public const FILE_MODE = 0700;

        private readonly App $app;
        private readonly string $host, $storage;
        private readonly PathConfig $pathConfig;
        private readonly ?UserDetailsDto $user;

        public function __construct(App $app)
        {
            $this->app = $app;
            $this->host = $app->request->uri->host();
            $this->pathConfig = $app->config->pathConfig();
            $this->user = $app->auth->getUserDetails();
            $this->storage = self::createShortcut($this->pathConfig->storage);
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
            $path = $this->pathTo($this->pathConfig->privateBucket . $bucket);
            $prefix = StaticAssetOwnership::createPrivateFilePrefix($this->user?->storageBucket);
            return $this->saveFile($file, $path, $prefix, $rename);
        }

        private function saveFile(File $file, string $path, ?string $prefix, bool $rename): string
        {
            $filename = $rename ? $prefix . StaticAssetOwnership::createBucketName() . $file->extension : $file->name;
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
            return $this->uri($this->pathConfig->publicBucket . $path);
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
            $owner = new StaticAssetOwnership($filepath);
            if ($this->user !== null && $owner->isOwner($this->user)) {
                return file_exists($filepath) && unlink($filepath);
            }
            return false;
        }

        #[\Override]
        public function share2protected(string $filepath): ?string
        {
            $prefix = StaticAssetOwnership::createProtectedFilePrefix($this->user?->storageBucket);
            $bucket = $this->pathConfig->privateBucket;
            return $this->shareFile($filepath, $bucket, $prefix);
        }

        #[\Override]
        public function share2group(string $filepath): ?string
        {
            $prefix = StaticAssetOwnership::createGroupFilePrefix($this->user?->storageBucket, $this->user?->groupStorageBucket);
            $bucket = $this->pathConfig->privateBucket;
            return $this->shareFile($filepath, $bucket, $prefix);
        }

        #[\Override]
        public function share2other(string $filepath, string $otherBucket): ?string
        {
            $prefix = StaticAssetOwnership::createPrivateFilePrefix($otherBucket);
            $bucket = $this->pathConfig->privateBucket;
            return $this->shareFile($filepath, $bucket, $prefix);
        }

        #[\Override]
        public function share2public(string $filepath): ?string
        {
            $prefix = StaticAssetOwnership::createPrivateFilePrefix($this->user->storageBucket);
            $bucket = $this->pathConfig->publicBucket;
            return $this->shareFile($filepath, $bucket, $prefix);
        }

        private function shareFile(string $filepath, string $bucket, string $prefix): ?string
        {
            $srcFile = $this->pathTo($filepath);
            if (is_readable($srcFile)) {
                $newName = $prefix . (new StaticAssetOwnership($filepath))->filename;
                $srcBucket = substr($filepath, 0, strpos($filepath, '/', 1));
                $savepath = $bucket . substr(dirname($filepath), strlen($srcBucket));
                $destination = self::createDirectory($this->pathTo($savepath));
                if (!is_link($destination . '/' . $newName) && symlink($srcFile, $destination . '/' . $newName)) {
                    return $savepath . '/' . $newName;
                }
            }
            return null;
        }
    }

}