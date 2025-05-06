<?php

/**
 * Description of ServerConfig
 * @author coder
 *
 * Created on: Mar 6, 2024 at 4:06:33 PM
 */

namespace shani {

    use shani\core\Definitions;
    use shani\core\VirtualHost;
    use shani\servers\swoole\SwooleServer;
    use test\TestConfig;

    final class HttpServer
    {

        private static array $mime = [], $hosts = [];

        public static function mime(string $extension): ?string
        {
            if (!isset(self::$mime[$extension])) {
                $mime = yaml_parse_file(Definitions::DIR_CONFIG . '/mime.yml')[$extension] ?? null;
                if ($mime === null) {
                    return null;
                }
                self::$mime[$extension] = $mime;
            }
            return self::$mime[$extension];
        }

        public static function host(string $name): VirtualHost
        {
            if (!empty(self::$hosts[$name])) {
                return self::$hosts[$name];
            }
            $yaml = Definitions::DIR_HOSTS . '/' . $name . '.yml';
            if (is_file($yaml)) {
                $config = new VirtualHost(yaml_parse_file($yaml));
                if ($config->cache) {
                    self::$hosts[$name] = $config;
                }
                return $config;
            }
            $alias = Definitions::DIR_HOSTS . '/' . $name . '.alias';
            if (is_file($alias)) {
                $host = file_get_contents($alias);
                return static::host(trim($host));
            }
            throw new \Exception('Host "' . $name . '" not found');
        }

        private static function checkFrameworkRequirements()
        {
            if (version_compare(Definitions::MIN_PHP_VERSION, PHP_VERSION) >= 0) {
                echo'Please version ' . Definitions::MIN_PHP_VERSION . ' or higher is required' . PHP_EOL;
                exit(1);
            }
            foreach (Definitions::REQUIRED_EXTENSIONS as $extension) {
                if (!extension_loaded($extension)) {
                    echo'Please install PHP ' . $extension . ' extension' . PHP_EOL;
                    exit(1);
                }
            }
        }

        /**
         * Starting the server. When started, server becomes ready to accept requests
         * @param array $arguments CLI arguments
         * @return void
         */
        public static function start(array $arguments): void
        {
            self::checkFrameworkRequirements();
            $config = new HttpServerConfig(yaml_parse_file(Definitions::DIR_CONFIG . '/server.yml'));
            $server = new SwooleServer($config);
            $result = null;
            $server->start(function () use (&$arguments, &$server, &$result) {
                echo 'Server started on ' . date(DATE_RSS) . PHP_EOL;
                $result = TestConfig::config($arguments);
                if ($result !== null) {
                    $server->stop();
                }
            });
            exit($result ? 0 : 1);
        }
    }

}
