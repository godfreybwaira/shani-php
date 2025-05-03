<?php

/**
 * Description of TestCase
 * @author coder
 *
 * Created on: May 3, 2025 at 9:49:06 AM
 */

namespace test {

    use shani\core\Definitions;
    use shani\core\VirtualHost;

    final class TestCase
    {

        private static string $source, $destination;

        public static function config(array $args): void
        {
            $params = self::getTestParams($args);
            if ($params === null) {
                return;
            }
            self::$source = Definitions::DIR_HOSTS . '/' . $params['host'] . '.yml';
            self::$destination = self::$source . '.bak';
            if (!copy(self::$source, self::$destination)) {
                throw new \Exception('Host not available.');
            }
            $content = yaml_parse_file(self::$source);
            if (!array_key_exists($params['env'], $content['ENVIRONMENTS'])) {
                throw new \Exception('Could not start a test because the environment "' . $params['env'] . '" is not found.');
            }
            if (file_put_contents(self::$source, yaml_emit($content)) === false) {
                self::endTest();
                throw new \Exception('Could not start a test.');
            }
            $content['CACHE'] = false;
            $content['ACTIVE_ENVIRONMENT'] = $params['env'];
            $vhost = new VirtualHost($content);
            $vhost->configFile::test();
        }

        public static function endTest(): void
        {
            if (is_file(self::$source) && is_file(self::$destination)) {
                rename(self::$destination, self::$source);
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
