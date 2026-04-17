<?php

/**
 * Configuring an automated unit test for application
 * @author coder
 *
 * Created on: May 3, 2025 at 9:49:06 AM
 */

namespace features\test\helpers {

    use features\logging\LoggingLevel;
    use shani\launcher\ApplicationLauncher;
    use shani\launcher\Framework;
    use shani\launcher\RequestPreference;

    final class TestRunner
    {

        private const TEST_FILE = Framework::DIR_STORAGE . '/.TE5T_15_RUNN1NG';

        public static function isRunning(): bool
        {
            return is_file(self::TEST_FILE);
        }

        private static function run(RequestPreference $preference): bool
        {
            $backupFile = sys_get_temp_dir() . '/' . bin2hex(random_bytes(20)) . '.bak';
            self::createBackupFile($preference->configFile, $backupFile);
            $content = $preference->vhost->toArray();
            $content['testmode'] = false;
            if (file_put_contents($preference->configFile, yaml_emit($content)) === false) {
                self::removeBackupFile($preference->configFile, $backupFile);
                throw new \Exception('Could not start a test, host file is not writable.');
            }
            $test = $preference->vhost->getOne('config')::runTest();
            self::removeBackupFile($preference->configFile, $backupFile);
            return $test->getResult();
        }

        public static function createBackupFile(string $originalFile, string $backupFile): void
        {
            if (!is_file($originalFile)) {
                self::stop();
                throw new \Exception('Source file not found: ' . $originalFile);
            }
            if (!is_file($backupFile) && !copy($originalFile, $backupFile)) {
                self::stop();
                throw new \Exception('Could not start a test because host file is not writable.');
            }
        }

        public static function removeBackupFile(string $originalFile, string $backupFile): void
        {
            if (is_file($originalFile) && is_file($backupFile)) {
                rename($backupFile, $originalFile);
            }
            self::stop();
        }

        /**
         * Run application test
         * @param RequestPreference $preference Client request preference
         * @return string Test result description and status
         */
        public static function start(RequestPreference $preference): string
        {
            touch(self::TEST_FILE);
            ApplicationLauncher::log(LoggingLevel::INFO, 'Test is running...');
            $passes = self::run($preference);
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
