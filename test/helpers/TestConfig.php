<?php

/**
 * Configuring an automated unit test for application
 * @author coder
 *
 * Created on: May 3, 2025 at 9:49:06 AM
 */

namespace test\helpers {

    use lib\ds\map\ReadableMap;
    use shani\ApplicationLauncher;
    use shani\core\Framework;
    use shani\core\log\LogLevel;

    final class TestConfig
    {

        private const TEST_FILE = Framework::DIR_STORAGE . '/__TEST_IS_RUNNING__';

        private static function config(TestParameters $params): bool
        {
            $source = Framework::DIR_HOSTS . '/' . $params->host . '.yml';
            $destination = sys_get_temp_dir() . '/' . basename($source) . '.bak';
            self::createBackupFile($source, $destination);
            $content = yaml_parse_file($source);
            if (file_put_contents($source, yaml_emit($content)) === false) {
                self::removeBackupFile($source, $destination);
                self::stop();
                throw new \Exception('Could not start a test.');
            }
            $content['profile'] = $params->profile;
            $vhost = new ReadableMap($content);
            self::removeBackupFile($source, $destination);
            $test = $vhost->getOne('classpath')::runTest();
            return $test->getResult();
        }

        public static function createBackupFile(string $source, string $destination): void
        {
            if (!is_file($source)) {
                self::stop();
                throw new \Exception('Host not available.');
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
        }

        public static function start(TestParameters $params): ?bool
        {
            if (is_file(self::TEST_FILE)) {
                return null;
            }
            touch(self::TEST_FILE);
            ApplicationLauncher::log(LogLevel::INFO, 'Test is running...');
            $result = self::config($params);
            self::stop();
            if ($result) {
                ApplicationLauncher::log(LogLevel::INFO, 'Test finished and passed.');
            } else {
                ApplicationLauncher::log(LogLevel::WARNING, 'Test finished and failed.');
            }
            return $result;
        }

        public static function stop(): void
        {
            if (is_file(self::TEST_FILE)) {
                unlink(self::TEST_FILE);
            }
        }
    }

}
