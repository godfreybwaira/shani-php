<?php

/**
 * Description of ApplicationLauncher
 * @author coder
 *
 * Created on: Mar 6, 2024 at 4:06:33 PM
 */

namespace shani\launcher {

    use features\utils\Concurrency;
    use features\ds\map\ReadableMap;
    use features\utils\Event;
    use shani\http\HttpHeader;
    use shani\http\enums\HttpStatus;
    use shani\http\RequestEntity;
    use shani\http\ResponseEntity;
    use shani\contracts\ResponseWriter;
    use shani\contracts\SupportedWebServer;
    use shani\launcher\Framework;
    use features\log\Logger;
    use features\log\LogLevel;
    use shani\launcher\App;
    use features\persistence\LocalStorage;
    use features\test\helpers\TestRunner;

    final class ApplicationLauncher
    {

        /**
         * Get Virtual host configurations
         * @param string $name Host name
         * @return ReadableMap
         * @throws \Exception
         */
        private static function host(string $name): ReadableMap
        {
            $yaml = Framework::DIR_HOSTS . '/' . $name . '.yml';
            if (is_file($yaml)) {
                return new ReadableMap(yaml_parse_file($yaml));
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
         * @return void
         */
        public static function start(SupportedWebServer $server): void
        {
            new Concurrency($server->getConcurrencyHandler());
            Event::setHandler($server->getEventHandler());
            $server->request(function (RequestEntity $request, ResponseWriter $writer, Framework $framework) {
                $hostname = $request->uri->hostname();
                $vhost = self::host($hostname);
                $response = new ResponseEntity($request, HttpStatus::OK, new HttpHeader(), new ReadableMap());
                if (!$vhost->getOne('testmode')) {
                    $app = new App($vhost, $response, $writer, $framework);
                    $app->launch();
                } else {
                    $msg = TestRunner::start($vhost, $hostname);
                    $response->setBody($msg);
                    $writer->close($response);
                }
            });
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

        public static function getClientIP(array &$httpHeaders, array $ipHeaders): ?string
        {
            foreach ($ipHeaders as $header) {
                if (!empty($httpHeaders[$header])) {
                    return explode(',', $httpHeaders[$header])[0];
                }
            }
            return null;
        }
    }

}
