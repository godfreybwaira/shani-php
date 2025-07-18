<?php

/**
 * Test execution environment
 * @author goddy
 *
 * Created on: Jul 18, 2025 at 12:09:47 PM
 */

namespace test {

    use shani\core\Framework;

    final class TestEnvironment implements \JsonSerializable
    {

        public const OS = PHP_OS_FAMILY;
        public const RUNTIME = 'PHP ' . PHP_VERSION;
        public const FRAMEWORK = Framework::NAME . ' ' . Framework::VERSION;

        #[\Override]
        public function jsonSerialize(): array
        {
            return [
                'os' => self::OS,
                'runtime' => self::RUNTIME,
                'framework' => self::FRAMEWORK
            ];
        }
    }

}
