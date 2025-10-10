<?php

/**
 * Configuring an automated unit test for application
 * @author coder
 *
 * Created on: May 3, 2025 at 9:49:06 AM
 */

namespace test\helpers {

    use shani\core\Framework;
    use shani\core\log\LogLevel;
    use shani\core\VirtualHost;
    use shani\WebServer;

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
            $content['CONFIGURATION']['PROFILE'] = $params->profile;
            $vhost = new VirtualHost($content);
            self::removeBackupFile($source, $destination);
            $test = $vhost->classFile::runTest();
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

        public static function start(TestParameters $params): void
        {
            if (is_file(self::TEST_FILE)) {
                return;
            }
            touch(self::TEST_FILE);
            WebServer::log(LogLevel::INFO, 'Test is running...');
            $result = self::config($params);
            self::stop();
            if ($result) {
                WebServer::log(LogLevel::INFO, 'Test finished and passed.');
            } else {
                WebServer::log(LogLevel::WARNING, 'Test finished and failed.');
            }
        }

        public static function stop(): void
        {
            if (is_file(self::TEST_FILE)) {
                unlink(self::TEST_FILE);
            }
        }
    }

}
