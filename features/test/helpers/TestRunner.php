<?php

/**
 * Configuring an automated unit test for application
 * @author coder
 *
 * Created on: May 3, 2025 at 9:49:06 AM
 */

namespace features\test\helpers {

    use features\ds\map\ReadableMap;
    use features\logging\LogLevel;
    use shani\launcher\ApplicationLauncher;
    use shani\launcher\Framework;

    final class TestRunner
    {

        private const TEST_FILE = Framework::DIR_STORAGE . '/.TE5T_15_RUNN1NG';

        public static function isRunning(): bool
        {
            return is_file(self::TEST_FILE);
        }

        private static function run(ReadableMap $vhost, string $hostname): bool
        {
            $source = self::getHostFilePath($hostname);
            $destination = sys_get_temp_dir() . '/' . basename($source) . '.bak';
            self::createBackupFile($source, $destination);
            $content = $vhost->toArray();
            $content['testmode'] = false;
            if (file_put_contents($source, yaml_emit($content)) === false) {
                self::removeBackupFile($source, $destination);
                throw new \Exception('Could not start a test, host file is not writable.');
            }
            $test = $vhost->getOne('config')::runTest();
            self::removeBackupFile($source, $destination);
            return $test->getResult();
        }

        private static function getHostFilePath(string $hostname): string
        {
            $alias = Framework::DIR_HOSTS . '/' . $hostname . '.alias';
            $filename = is_file($alias) ? trim(file_get_contents($alias)) : $hostname;
            return Framework::DIR_HOSTS . '/' . $filename . '.yml';
        }

        public static function createBackupFile(string $source, string $destination): void
        {
            if (!is_file($source)) {
                self::stop();
                throw new \Exception('Source file not found: ' . $source);
            }
            if (!is_file($destination) && !copy($source, $destination)) {
                self::stop();
                throw new \Exception('Could not start a test because host file is not writable.');
            }
        }

        public static function removeBackupFile(string $source, string $destination): void
        {
            if (is_file($source) && is_file($destination)) {
                rename($destination, $source);
            }
            self::stop();
        }

        /**
         * Run application test
         * @param ReadableMap $vhost Host configuration
         * @param string $hostname Host name
         * @return string Test result description and status
         */
        public static function start(ReadableMap $vhost, string $hostname): string
        {
            touch(self::TEST_FILE);
            ApplicationLauncher::log(LogLevel::INFO, 'Test is running...');
            $passes = self::run($vhost, $hostname);
            if ($passes) {
                return 'Test finished and passed.';
            }
            return 'Test finished and failed.';
        }

        public static function stop(): void
        {
            if (is_file(self::TEST_FILE)) {
                unlink(self::TEST_FILE);
            }
        }
    }

}
