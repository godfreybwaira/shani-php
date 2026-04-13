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

        public static function isRunning(): bool
        {
            return is_file(self::TEST_FILE);
        }

        /**
         * Run application test
         * @param ReadableMap $vhost Host configuration
         * @return string Test result description and status
         */
        public static function start(ReadableMap $vhost): string
        {
            touch(self::TEST_FILE);
            ApplicationLauncher::log(LogLevel::INFO, 'Test is running...');
            $test = $vhost->getOne('config')::runTest();
            self::stop();
            if ($test->getResult()) {
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
