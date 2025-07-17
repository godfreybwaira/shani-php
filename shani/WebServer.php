<?php

/**
 * Description of ServerConfig
 * @author coder
 *
 * Created on: Mar 6, 2024 at 4:06:33 PM
 */

namespace shani {

    use lib\Concurrency;
    use lib\Event;
    use lib\http\HttpHeader;
    use lib\http\HttpStatus;
    use lib\http\RequestEntity;
    use lib\http\ResponseEntity;
    use shani\contracts\ResponseWriter;
    use shani\contracts\SupportedWebServer;
    use shani\core\Framework;
    use shani\core\log\Logger;
    use shani\core\log\LogLevel;
    use shani\core\VirtualHost;
    use shani\http\App;
    use test\TestConfig;

    final class WebServer
    {

        private static array $mime = [], $hosts = [];

        public static function mime(string $extension): ?string
        {
            if (!isset(self::$mime[$extension])) {
                $mime = yaml_parse_file(Framework::DIR_CONFIG . '/mime.yml')[$extension] ?? null;
                if ($mime === null) {
                    return null;
                }
                self::$mime[$extension] = $mime;
            }
            return self::$mime[$extension];
        }

        private static function host(string $name): VirtualHost
        {
            if (!empty(self::$hosts[$name])) {
                return self::$hosts[$name];
            }
            $yaml = Framework::DIR_HOSTS . '/' . $name . '.yml';
            if (is_file($yaml)) {
                $config = new VirtualHost(yaml_parse_file($yaml));
                if ($config->cache) {
                    self::$hosts[$name] = $config;
                }
                return $config;
            }
            $alias = Framework::DIR_HOSTS . '/' . $name . '.alias';
            if (is_file($alias)) {
                $host = file_get_contents($alias);
                return static::host(trim($host));
            }
            throw new \Exception('Host "' . $name . '" not found');
        }

        /**
         * Starting the server. When started, server becomes ready to accept requests
         * @param SupportedWebServer $server Server application capable of handling HTTP requests
         * @param array $args CLI arguments
         * @return void
         */
        public static function start(SupportedWebServer $server, array $args = null): void
        {
            new Concurrency($server->getConcurrencyHandler());
            Event::setHandler($server->getEventHandler());
            $server->request(function (RequestEntity $request, ResponseWriter $writer) {
                $response = new ResponseEntity($request, HttpStatus::OK, new HttpHeader());
                try {
                    $vhost = self::host($request->uri->hostname());
                    $app = new App($vhost, $response, $writer);
                    $app->runApp();
                } catch (\Throwable $ex) {
                    $response->setStatus(HttpStatus::INTERNAL_SERVER_ERROR)->header()
                            ->addOne(HttpHeader::SERVER, Framework::NAME);
                    $writer->close($response);
                    self::log(LogLevel::EMERGENCY, $ex->getMessage());
                }
            });
            $server->start(fn() => empty($args) ? null : TestConfig::start($args));
        }

        public static function log(LogLevel $level, string $message): void
        {
            if (PHP_SAPI === 'cli') {
                echo $message . PHP_EOL;
            }
            if (!is_dir(Framework::DIR_SERVER_STORAGE)) {
                mkdir(Framework::DIR_SERVER_STORAGE, persistence\LocalStorage::FILE_MODE);
            }
            $file = Framework::DIR_SERVER_STORAGE . '/' . date('Y-m-d') . '_' . $level->value . '.log';
            (new Logger($file))->log($level, $message);
        }
    }

}
