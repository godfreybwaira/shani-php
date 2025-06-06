<?php

/**
 * Configuring an automated unit test for application
 * @author coder
 *
 * Created on: May 3, 2025 at 9:49:06 AM
 */

namespace test {

    use shani\core\Framework;
    use shani\core\VirtualHost;

    final class TestConfig
    {

        public static function config(array $args): ?bool
        {
            $params = self::getTestParams($args);
            if ($params === null) {
                return null;
            }
            $source = Framework::DIR_HOSTS . '/' . $params['host'] . '.yml';
            $destination = $source . '.bak';
            self::createBackupFile($source, $destination);
            $content = yaml_parse_file($source);
            if (!array_key_exists($params['env'], $content['ENVIRONMENTS'])) {
                throw new \Exception('Could not start a test because the environment "' . $params['env'] . '" is not found.');
            }
            if (file_put_contents($source, yaml_emit($content)) === false) {
                self::removeBackupFile($source, $destination);
                throw new \Exception('Could not start a test.');
            }
            $content['CACHE_CONFIG'] = false;
            $content['ACTIVE_ENVIRONMENT'] = $params['env'];
            $vhost = new VirtualHost($content);
            self::removeBackupFile($source, $destination);
            return TestResult::processResult($vhost->configFile::runTest());
        }

        public static function createBackupFile(string $source, string $destination): void
        {
            if (!is_file($source)) {
                throw new \Exception('Host not available.');
            }
            if (!is_file($destination) && !copy($source, $destination)) {
                throw new \Exception('Could not start a test because host file is not writable.');
            }
        }

        public static function removeBackupFile(string $source, string $destination): void
        {
            if (is_file($source) && is_file($destination)) {
                rename($destination, $source);
            }
        }

        private static function getTestParams(array &$params): ?array
        {
            $values = [];
            $size = count($params);
            for ($i = 1; $i < $size; $i++) {
                if ($params[$i] === '--test') {
                    $values['host'] = $params[++$i];
                } else if ($params[$i] === '--env') {
                    $values['env'] = $params[++$i];
                }
            }
            return !empty($values) ? $values : null;
        }
    }

}
