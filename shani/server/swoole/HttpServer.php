<?php

/**
 * Shani HTTP server powered by swoole
 * @author coder
 *
 * Created on: Mar 6, 2024 at 3:42:29 PM
 */

namespace shani\server\swoole {

    use library\Concurrency;
    use library\DataConvertor;
    use library\Event;
    use library\http\HttpHeader;
    use library\http\HttpStatus;
    use library\http\ResponseEntity;
    use library\Map;
    use library\MediaType;
    use library\RequestEntityBuilder;
    use library\URI;
    use library\Utils;
    use shani\engine\core\Definitions;
    use shani\engine\http\App;
    use shani\engine\http\UploadedFile;
    use shani\ServerConfig;
    use Swoole\Http\Request;
    use Swoole\Http\Response;
    use Swoole\WebSocket\Frame;
    use Swoole\WebSocket\Server as WSocket;

    final class HttpServer
    {

        private const SOCKET_TCP = 1, SSL = 512;
        private const SCHEDULING = ['ROUND_ROBIN' => 1, 'PREEMPTIVE' => 3, 'FIXED' => 2];

        private static function configure(array $cnf): WSocket
        {
            ini_set('display_errors', $cnf['DISPLAY_ERRORS']);
            new Concurrency(new SwooleConcurrency());

            Event::setHandler(new SwooleEvent());
            MediaType::setHandler(new SwooleCache(1500, 100));
            $server = new WSocket($cnf['IP'], $cnf['SERVER_PORTS']['HTTP']);
            $cores = swoole_cpu_num();
            $server->set([
                'task_worker_num' => $cores, 'reactor_num' => $cores,
                'worker_num' => $cores, 'enable_coroutine' => true,
                'reload_async' => true, 'max_wait_time' => (int) $cnf['MAX_WAIT_TIME'],
                'open_http2_protocol' => $cnf['ENABLE_HTTP2'], 'backlog' => $cores * 30, // number of connections in queue
                'max_request' => (int) $cnf['MAX_WORKER_REQUESTS'], 'http_compression' => true,
                'max_conn' => (int) $cnf['MAX_CONNECTIONS'], 'task_enable_coroutine' => true,
                'http_compression_level' => 3, 'daemonize' => $cnf['RUNAS_DAEMON'],
                'dispatch_mode' => self::SCHEDULING[$cnf['SCHEDULING_ALGORITHM']],
                'websocket_compression' => true, 'ssl_allow_self_signed' => true,
                'ssl_cert_file' => str_replace('${SSL_DIR}', Definitions::DIR_SSL, $cnf['SSL']['CERT']),
                'ssl_key_file' => str_replace('${SSL_DIR}', Definitions::DIR_SSL, $cnf['SSL']['KEY'])
            ]);
            $server->addListener($cnf['IP'], $cnf['SERVER_PORTS']['HTTPS'], self::SOCKET_TCP | self::SSL);
            return $server;
        }

        private static function makeURI(string $scheme, string $host, array &$server): URI
        {
            $query = !empty($server['query_string']) ? '?' . $server['query_string'] : null;
            $path = $scheme . '://' . $host . $server['path_info'] . $query;
            return new URI($path);
        }

        private static function handleHTTP(string $scheme, Request &$req, Response &$res)
        {
            $uri = self::makeURI($scheme, $req->header['host'], $req->server);
            $request = (new RequestEntityBuilder())
                    ->protocol($req->server['server_protocol'])
                    ->method($req->server['request_method'])
                    ->headers(new HttpHeader($req->header))
                    ->cookies(Map::normalize($req->cookie))
                    ->time($req->server['request_time'])
                    ->files(self::getFiles($req->files))
                    ->query(Map::normalize($req->get))
                    ->ip($req->server['remote_addr'])
                    ->body(self::getBody($req))
                    ->uri($uri)
                    ->build();
            $response = new ResponseEntity($request, HttpStatus::OK, new HttpHeader());
            new App($response, new SwooleResponseWriter($res));
        }

        private static function getBody(Request &$req): ?array
        {
            $inputs = Map::normalize($req->post);
            if (!empty($inputs)) {
                return $inputs;
            }
            $contentType = $req->header['content-type'] ?? null;
            if (empty($contentType)) {
                return null;
            }
            $type = MediaType::explode(strtolower($contentType))[1];
            return DataConvertor::convertFrom($req->rawcontent(), $type);
        }

        private static function getFiles(?array $files): array
        {
            $uploaded = [];
            if (!empty($files)) {
                foreach ($files as $name => $file) {
                    $uploaded[$name] = new UploadedFile(
                            path: $file['tmp_name'], type: $file['type'],
                            size: $file['size'], name: $file['name'],
                            error: $file['error']
                    );
                }
            }
            return $uploaded;
        }

        private static function checkFrameworkRequirements()
        {
            if (version_compare(Definitions::MIN_PHP_VERSION, PHP_VERSION) >= 0) {
                exit('PHP version ' . Definitions::MIN_PHP_VERSION . ' or higher is required' . PHP_EOL);
            }
            foreach (Definitions::REQUIRED_EXTENSIONS as $extension) {
                if (!extension_loaded($extension)) {
                    exit('Please install PHP ' . $extension . ' extension' . PHP_EOL);
                }
            }
        }

        /**
         * Starting the server. When started, server becomes ready to accept requests
         * @return void
         */
        public static function start(): void
        {
            self::checkFrameworkRequirements();
            Utils::errorHandler();
            $cnf = ServerConfig::server();
            $server = self::configure($cnf);
            $server->on('start', function () use (&$cnf) {
                echo 'http://' . $cnf['IP'] . ':' . $cnf['SERVER_PORTS']['HTTP'] . PHP_EOL;
                echo 'https://' . $cnf['IP'] . ':' . $cnf['SERVER_PORTS']['HTTPS'] . PHP_EOL;
            });
            $server->on('request', function (Request $req, Response $res) use (&$cnf) {
                $scheme = $cnf['SERVER_PORTS']['HTTP'] === $req->server['server_port'] ? 'http' : 'https';
                self::handleHTTP($scheme, $req, $res);
            });
            $server->on('open', function (WSocket $server, Request $req) {

            });
            $server->on('message', function (WSocket $server, Frame $frame) {

            });
            $server->on('close', function (WSocket $server, int $fd) {

            });
            $server->on('task', fn() => null);
            $server->start();
        }
    }

}