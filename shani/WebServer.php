<?php

/**
 * Description of ServerConfig
 * @author coder
 *
 * Created on: Mar 6, 2024 at 4:06:33 PM
 */

namespace shani {

    use lib\Concurrency;
    use lib\ds\map\ReadableMap;
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
    use shani\persistence\LocalStorage;
    use test\helpers\TestConfig;
    use test\helpers\TestParameters;

    final class WebServer
    {

        private static array $mime = [];

        public static function mime(string $extension): ?string
        {
            $ext = strtolower($extension);
            if (!isset(self::$mime[$ext])) {
                $mime = yaml_parse_file(Framework::DIR_CONFIG . '/mime.yml')[$ext] ?? null;
                if ($mime === null) {
                    return null;
                }
                self::$mime[$ext] = $mime;
            }
            return self::$mime[$ext];
        }

        private static function host(string $name): VirtualHost
        {
            $yaml = Framework::DIR_HOSTS . '/' . $name . '.yml';
            if (is_file($yaml)) {
                return new VirtualHost(yaml_parse_file($yaml));
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
         * @param TestParameters $params Test parameters
         * @return void
         */
        public static function start(SupportedWebServer $server, TestParameters $params = null): void
        {
            new Concurrency($server->getConcurrencyHandler());
            Event::setHandler($server->getEventHandler());
            $server->request(function (RequestEntity $request, ResponseWriter $writer, FrameworkConfig $framework) {
                $response = new ResponseEntity($request, HttpStatus::OK, new HttpHeader(), new ReadableMap());
                try {
                    $vhost = self::host($request->uri->hostname());
                    $app = new App($vhost, $response, $writer, $framework);
                    $app->runApp();
                } catch (\Throwable $ex) {
                    $response->setStatus(HttpStatus::INTERNAL_SERVER_ERROR)->header()
                            ->addOne(HttpHeader::SERVER, Framework::NAME);
                    $writer->close($response);
                    self::log(LogLevel::EMERGENCY, $ex->getMessage());
                }
            });
            $server->start(fn() => $params === null ? null : TestConfig::start($params));
        }

        public static function log(LogLevel $level, string $message): void
        {
            if (PHP_SAPI === 'cli') {
                echo $message . PHP_EOL;
            }
            if (!is_dir(Framework::DIR_SERVER_STORAGE)) {
                mkdir(Framework::DIR_SERVER_STORAGE, LocalStorage::FILE_MODE, true);
            }
            $file = Framework::DIR_SERVER_STORAGE . '/' . date('Y-m-d') . '_' . $level->value . '.log';
            (new Logger($file))->log($level, $message);
        }
    }

}
