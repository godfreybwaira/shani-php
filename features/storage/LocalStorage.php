<?php

/**
 * Description of LocalStorage
 * @author coder
 *
 * Created on: Feb 11, 2024 at 6:50:02 PM
 */

namespace features\storage {

    use features\assets\StaticAssetOwnership;
    use features\authentication\UserDetailsDto;
    use features\exceptions\ServerException;
    use features\storage\StorageMediaInterface;
    use features\utils\Concurrency;
    use features\utils\File;
    use features\utils\URI;
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
        public const FILE_MODE = 0755;

        /** @var App Application context */
        private readonly App $app;

        /** @var string Hostname extracted from request URI */
        private readonly string $host;

        /** @var string Storage base path */
        private readonly string $storage;

        /** @var PathConfig Configuration object containing bucket paths */
        private readonly PathConfig $pathConfig;

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
            $this->storage = self::createShortcut($this->pathConfig->storage);
        }

        /**
         * Automates the creation of a symbolic link between an application's internal
         * storage and the framework's global storage directory.
         * * This method extracts the application name from the target path to create
         * a clean shortcut. For example, a target of 'apps/demo/bucket' will
         * result in a shortcut at 'storage/demo/bucket'.
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
        public function save(File $file, string $bucket = null, bool $rename = true): string
        {
            $userBucket = $this->app->auth->getUserDetails()->storageBucket;
            $prefix = StaticAssetOwnership::createPrivateFilePrefix($userBucket);
            $destination = $this->pathTo($this->pathConfig->privateBucket . '/' . trim($bucket, '/'));
            return $this->saveFile($file, rtrim($destination, '/'), $prefix, $rename, function ($filepath)use (&$file) {
                        if (move_uploaded_file($file->path, $filepath)) {
                            return;
                        }
                        $to = fopen($filepath, 'wb');
                        $from = fopen($file->path, 'rb');
                        $chunk = min($file->size, Framework::BUFFER_SIZE);
                        while (!feof($from)) {
                            fwrite($to, fread($from, $chunk));
                        }
                        fclose($from);
                        fclose($to);
                    });
        }

        /**
         * Save a file to the given destination.
         *
         * This method builds the target file path using `createFilePath()`, then executes
         * the provided callback (e.g., `rename()` or `symlink()`) to perform the actual
         * file operation. It returns the relative path (trimmed against the storage root).
         *
         * @param File    $file       The file object containing metadata and source path.
         * @param string  $destination The destination directory where the file should be saved.
         * @param string  $prefix      A prefix to prepend to the file name for uniqueness or categorization.
         * @param bool    $rename      Whether to rename the file (true) or keep its original name (false).
         * @param \Closure $callback   A callback that performs the file operation. Typically `rename()` or `symlink()`.
         *
         * @return string Relative path of the saved file, excluding the storage root.
         */
        private function saveFile(File $file, string $destination, string $prefix, bool $rename, \Closure $callback): string
        {
            $filepath = self::createFilePath($file, $destination, $prefix, $rename);
            $callback($filepath);
            return substr($filepath, strlen($this->storage));
        }

        /**
         * Share a file by creating a symbolic link at the destination.
         *
         * This method wraps `saveFile()` with a symlink operation, ensuring the file
         * is linked rather than moved or copied. Useful for sharing without duplicating data.
         *
         * @param File   $file        The file object containing metadata and source path.
         * @param string $destination The destination directory where the symlink should be created.
         * @param string $prefix      A prefix to prepend to the symlink name for uniqueness or categorization.
         *
         * @return string Relative path of the symlink, excluding the storage root.
         */
        private function shareFile(File $file, string $destination, string $prefix): string
        {
            return $this->saveFile($file, $destination, $prefix, true, fn($shortcut) => symlink($file->path, $shortcut));
        }

        /**
         * Build a full file path for saving or linking a file.
         *
         * This method determines the filename based on whether renaming is requested.
         * If `$rename` is true, it generates a bucket name via `StaticAssetOwnership::createBucketName()`
         * and appends the file extension. Otherwise, it uses the original file name.
         * It then ensures the destination subdirectory (based on file type) exists,
         * creating it if necessary, and returns the full path including prefix.
         *
         * @param File   $file        File object containing metadata.
         * @param string $destination Base destination directory where the file should be stored.
         * @param string $prefix      Prefix to prepend to the filename for uniqueness or categorization.
         * @param bool   $rename      Whether to rename the file using a generated bucket name.
         *
         * @return string Full filesystem path for the file (including directory, prefix, and filename).
         *
         * @throws ServerException If the destination directory cannot be created.
         */
        private static function createFilePath(File $file, string $destination, string $prefix, bool $rename): string
        {
            $filename = $rename ? StaticAssetOwnership::createBucketName() . $file->extension : $file->name;
            $directory = $destination . '/' . $file->type;
            if (is_dir($directory) || mkdir($directory, self::FILE_MODE, true)) {
                return $directory . '/' . $prefix . $filename;
            }
            throw new ServerException('Failed to create directory: ' . $directory);
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
        public function delete(File $file): bool
        {
            $owner = new StaticAssetOwnership($file->name);
            $user = $this->app->auth->getUserDetails();
            if ($user !== null && $owner->isOwner($user)) {
                return unlink($file->path);
            }
            return false;
        }

        #[\Override]
        public function share2protected(File $file): ?string
        {
            $userBucket = $this->app->auth->getUserDetails()->storageBucket;
            $prefix = StaticAssetOwnership::createProtectedFilePrefix($userBucket);
            $destination = $this->pathTo($this->pathConfig->privateBucket);
            return $this->shareFile($file, $destination, $prefix);
        }

        #[\Override]
        public function share2group(File $file): ?string
        {
            $user = $this->app->auth->getUserDetails();
            $prefix = StaticAssetOwnership::createGroupFilePrefix($user->storageBucket, $user->groupStorageBucket);
            $destination = $this->pathTo($this->pathConfig->privateBucket);
            return $this->shareFile($file, $destination, $prefix);
        }

        #[\Override]
        public function share2other(File $file, string $otherBucket): ?string
        {
            $prefix = StaticAssetOwnership::createPrivateFilePrefix($otherBucket);
            $destination = $this->pathTo($this->pathConfig->privateBucket);
            return $this->shareFile($file, $destination, $prefix);
        }

        #[\Override]
        public function share2public(File $file): ?string
        {
            $userBucket = $this->app->auth->getUserDetails()->storageBucket;
            $prefix = StaticAssetOwnership::createPrivateFilePrefix($userBucket);
            $destination = $this->pathTo($this->pathConfig->protectedBucket);
            return $this->shareFile($file, $destination, $prefix);
        }
    }

}