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

    final class TestRunner
    {

        private const TEST_FILE = Framework::DIR_STORAGE . '/__TEST_IS_RUNNING__';

        public static function stillRunning(): bool
        {
            return is_file(self::TEST_FILE);
        }

        /**
         * Run application test
         * @param ReadableMap $vhost Host configuration
         */
        public static function start(ReadableMap $vhost): void
        {
            touch(self::TEST_FILE);
            ApplicationLauncher::log(LogLevel::INFO, 'Test is running...');
            $test = $vhost->getOne('config')::runTest($vhost->getOne('profile'));
            self::stop();
            if ($test->getResult()) {
                ApplicationLauncher::log(LogLevel::INFO, 'Test finished and passed.');
            } else {
                ApplicationLauncher::log(LogLevel::WARNING, 'Test finished and failed.');
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
