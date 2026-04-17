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
    use features\logging\Logger;
    use features\logging\LoggingLevel;
    use shani\launcher\App;
    use features\persistence\LocalStorage;
    use features\test\helpers\TestRunner;

    final class ApplicationLauncher
    {

        private static function getConfigPreference(string $hostname, array $host, HttpHeader $headers): ?RequestPreference
        {
            $version = $headers->getOne($host['version']['request_header'], $host['version']['default']);
            $filename = $host['version']['supported'][$version] ?? null;
            $filepath = Framework::DIR_HOSTS . '/' . $hostname . '/' . $filename;
            if (is_file($filepath)) {
                $vhost = new ReadableMap(yaml_parse_file($filepath));
                return new RequestPreference($version, $vhost, $host['version']['request_header'], $host['version']['response_header']);
            }
            return null;
        }

        /**
         * Get Virtual host configurations
         * @param string $name Host name
         * @param HttpHeader $headers HTTP request headers
         * @return RequestPreference|null
         * @throws \Exception
         */
        private static function host(string $name, HttpHeader $headers): ?RequestPreference
        {
            $yaml = Framework::DIR_HOSTS . '/' . $name . '.yml';
            if (is_file($yaml)) {
                return self::getConfigPreference($name, yaml_parse_file($yaml), $headers);
            }
            $alias = Framework::DIR_HOSTS . '/' . $name . '.alias';
            if (is_file($alias)) {
                $host = file_get_contents($alias);
                return static::host(trim($host), $headers);
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
                $responseHeader = new HttpHeader();
                $response = new ResponseEntity($request, HttpStatus::OK, $responseHeader, new ReadableMap());
                $hostname = $request->uri->hostname();
                $preference = self::host($hostname, $request->header());
                if ($preference === null) {
                    $response->setStatus(HttpStatus::BAD_REQUEST)->setBody('Unsupported application version.');
                    $writer->send($response);
                    return;
                }
                $responseHeader->addOne(HttpHeader::VARY, $preference->requestVersionHeader);
                if ($preference->contentVersionHeader !== null) {
                    $responseHeader->addOne($preference->contentVersionHeader, $preference->appVersion);
                }
                if (!$preference->vhost->getOne('testmode')) {
                    $app = new App($preference->vhost, $response, $writer, $framework);
                    $app->launch();
                } else {
                    $msg = TestRunner::start($preference->vhost, $hostname);
                    $response->setBody($msg);
                    $writer->close($response);
                }
            });
        }

        public static function log(LoggingLevel $level, string $message): void
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
