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

    /**
     * LocalStorage provides an implementation of StorageMediaInterface
     * for managing file storage operations such as saving, deleting,
     * and sharing files across different buckets (private, public, group, etc.).
     *
     * It handles directory creation, symbolic links, and ensures
     * ownership rules are respected when performing file operations.
     */
    final class LocalStorage implements StorageMediaInterface
    {

        /** @var int Default file mode for created directories */
        public const FILE_MODE = 0700;

        /** @var App Application context */
        private readonly App $app;

        /** @var string Hostname extracted from request URI */
        private readonly string $host;

        /** @var string Storage base path */
        private readonly string $storage;

        /** @var PathConfig Configuration object containing bucket paths */
        private readonly PathConfig $pathConfig;

        /** @var UserDetailsDto|null Authenticated user details */
        private readonly ?UserDetailsDto $user;

        /**
         * Constructor initializes storage paths and user context.
         *
         * @param App $app Application instance providing request, config, and auth context
         */
        public function __construct(App $app)
        {
            $this->app = $app;
            $this->host = $app->request->uri->host();
            $this->pathConfig = $app->config->pathConfig();
            $this->user = $app->auth->getUserDetails();
            $this->storage = self::createShortcut($this->pathConfig->storage);
        }

        /**
         * Create a symbolic link shortcut to the storage directory.
         *
         * @param string $target Target directory path
         * @return string Shortcut path
         * @throws ServerException If directory or symlink creation fails
         */
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

        #[\Override]
        public function save(File $file, string $bucket = '/', bool $rename = true): string
        {
            $path = $this->pathTo($this->pathConfig->privateBucket . $bucket);
            $prefix = StaticAssetOwnership::createPrivateFilePrefix($this->user?->storageBucket);
            return $this->saveFile($file, $path, $prefix, $rename);
        }

        /**
         * Internal helper to save a file to a given path.
         *
         * @param File $file File object
         * @param string $path Destination path
         * @param string|null $prefix Optional filename prefix
         * @param bool $rename Whether to rename the file
         * @return string Relative path of saved file
         */
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

        /**
         * Create a directory if it does not exist.
         *
         * @param string $destination Directory path
         * @return string Created directory path
         * @throws ServerException If directory creation fails
         */
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