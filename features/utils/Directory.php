<?php

/**
 * Description of Directory
 * @author goddy
 *
 * Created on: May 1, 2026 at 5:59:12 PM
 */

namespace features\utils {

    use features\storage\LocalStorage;

    final class Directory
    {

        /**
         * Recursively copy a directory and all of its contents.
         *
         * This function will copy all files and subdirectories from the source
         * directory into the destination directory. If the destination does not
         * exist, it will be created automatically.
         *
         * @param string $source      Path to the source directory.
         * @param string $destination Path to the destination directory.
         *
         * @return bool Returns true on success, false if the source directory does not exist.
         */
        public static function copy(string $source, string $destination): bool
        {
            if (!is_dir($source)) {
                return false;
            }
            if (!is_dir($destination)) {
                mkdir($destination, LocalStorage::FILE_MODE, true);
            }
            $dir = opendir($source);
            while (($file = readdir($dir)) !== false) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                $srcFile = $source . DIRECTORY_SEPARATOR . $file;
                $destFile = $destination . DIRECTORY_SEPARATOR . $file;
                if (is_dir($srcFile)) {
                    self::copy($srcFile, $destFile);
                } else {
                    copy($srcFile, $destFile);
                }
            }
            closedir($dir);
            return true;
        }
    }

}
