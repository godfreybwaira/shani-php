<?php

/**
 * Description of ApplicationLauncher
 * @author coder
 *
 * Created on: Mar 6, 2024 at 4:06:33 PM
 */

namespace shani\launcher {

    use features\cache\CacheFactory;
    use features\ds\map\ReadableMap;
    use features\utils\Concurrency;
    use features\utils\Duration;
    use features\utils\Event;
    use shani\contracts\ResponseWriterInterface;
    use shani\http\enums\HttpStatus;
    use shani\http\HttpHeader;
    use shani\http\RequestEntity;
    use shani\http\ResponseEntity;
    use shani\launcher\App;
    use shani\launcher\Framework;
    use shani\servers\SupportedWebServer;
    use shani\utils\RequestPreference;
    use shani\utils\VirtualHostMapper;

    /**
     * Handles application startup, virtual host resolution, and request dispatching.
     *
     * The ApplicationLauncher is responsible for:
     * - Resolving virtual host configurations from YAML or alias files
     * - Determining request preferences based on version headers
     * - Starting the supported web server and binding request handlers
     * - Logging server events and messages
     * - Resolving client IP addresses from HTTP headers
     *
     * It acts as the entry point for launching the application server and
     * orchestrating request routing to the App class.
     */
    final class ApplicationLauncher
    {

        /**
         * Resolve configuration preference for a given host and request headers.
         *
         * @param string $hostName Host name being requested.
         * @param VirtualHostMapper $mapper Virtual host configurations from host file.
         * @param HttpHeader $headers HTTP request headers.
         * @return RequestPreference|null Request preference object or null if unsupported.
         */
        private static function getConfigPreference(string $hostName, VirtualHostMapper $mapper, HttpHeader $headers): ?RequestPreference
        {
            $version = $headers->getOne($mapper->requestHeader, $mapper->defaultVersion);
            if (!empty($mapper->supportedVersions[$version])) {
                return new RequestPreference($version, $mapper, $hostName);
            }
            return null;
        }

        /**
         * Get virtual host configurations.
         *
         * @param string $hostName Host name.
         * @return array Host Configurations.
         * @throws \Exception If host configuration cannot be found.
         */
        private static function getHostConfigurations(string $hostName): array
        {

            $yaml = Framework::DIR_HOSTS . DIRECTORY_SEPARATOR . $hostName . '.yml';
            if (is_file($yaml)) {
                $config = CacheFactory::container()->fetch($hostName . filemtime($yaml), Duration::ofMonths(3), fn() => yaml_parse_file($yaml));
                return ['host' => $hostName, 'data' => $config];
            }
            $alias = Framework::DIR_HOSTS . DIRECTORY_SEPARATOR . $hostName . '.alias';
            if (is_file($alias)) {
                $host = file_get_contents($alias);
                return static::getHostConfigurations(trim($host));
            }
            throw new \RuntimeException('Host "' . $hostName . '" not found');
        }

        /**
         * Get virtual host configurations.
         *
         * @param string $hostName Host name.
         * @param HttpHeader $headers HTTP request headers.
         * @return RequestPreference|null Request preference object or null.
         * @throws \Exception If host configuration cannot be found.
         */
        private static function host(string $hostName, HttpHeader $headers): ?RequestPreference
        {
            $configs = self::getHostConfigurations($hostName);
            $mapper = VirtualHostMapper::fromArray($configs['data']);
            return self::getConfigPreference($configs['host'], $mapper, $headers);
        }

        /**
         * Start the server. When started, the server becomes ready to accept requests.
         *
         * @param SupportedWebServer $server Server application capable of handling HTTP requests.
         * @return void
         */
        public static function start(SupportedWebServer $server): void
        {
            new Concurrency($server->getConcurrencyHandler());
            Event::setHandler($server->getEventHandler());
            $server->request(function (RequestEntity $request, ResponseWriterInterface $writer, Framework $framework) {
                $response = new ResponseEntity($request, HttpStatus::OK, new HttpHeader(), new ReadableMap());
                $preference = self::host($request->uri->hostname(), $request->header());
                if ($preference === null) {
                    $response->setStatus(HttpStatus::BAD_REQUEST)->setBody('Unsupported application version');
                    $writer->send($response);
                    return;
                }
                $response->header()->addOne(HttpHeader::VARY, $preference->mapper->requestHeader);
                if ($preference->mapper->responseHeader !== null) {
                    $response->header()->addAll([
                        $preference->mapper->responseHeader => $preference->versionNumber,
                        HttpHeader::ACCESS_CONTROL_EXPOSE_HEADERS => $preference->mapper->responseHeader
                    ]);
                }
                $app = new App($preference, $response, $writer, $framework);
                $app->launch();
            });
        }

        /**
         * Get client IP address from HTTP headers.
         *
         * @param array $httpHeaders HTTP headers array.
         * @param array $ipHeaders List of header keys to check for IP.
         * @return string|null Client IP address or null if not found.
         */
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
