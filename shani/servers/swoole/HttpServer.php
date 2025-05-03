<?php

/**
 * Shani HTTP server powered by swoole
 * @author coder
 *
 * Created on: Mar 6, 2024 at 3:42:29 PM
 */

namespace shani\servers\swoole {

    use lib\Concurrency;
    use lib\DataConvertor;
    use lib\Event;
    use lib\File;
    use lib\http\HttpHeader;
    use lib\http\HttpStatus;
    use lib\http\ResponseEntity;
    use lib\MediaType;
    use lib\RequestEntityBuilder;
    use lib\URI;
    use shani\contracts\ResponseWriter;
    use shani\core\Definitions;
    use shani\http\App;
    use shani\ServerConfig;
    use Swoole\Http\Request;
    use Swoole\Http\Response;
    use Swoole\WebSocket\Frame;
    use Swoole\WebSocket\Server as WSocket;
    use test\TestCase;

    final class HttpServer
    {

        private const SOCKET_TCP = 1, SSL = 512;
        private const SCHEDULING = ['ROUND_ROBIN' => 1, 'FIXED' => 2, 'PREEMPTIVE' => 3, 'IPMOD' => 4];

        private static function configure(ServerConfig $cnf): WSocket
        {
            ini_set('display_errors', $cnf->showErrors);
            date_default_timezone_set($cnf->timezone);
            new Concurrency(new SwooleConcurrency());

            Event::setHandler(new SwooleEvent());
            $server = new WSocket($cnf->ip, $cnf->portHttp);
            $cores = swoole_cpu_num();
            $server->set([
                'task_worker_num' => $cores, 'reactor_num' => $cores,
                'worker_num' => $cores, 'enable_coroutine' => true,
                'reload_async' => true, 'max_wait_time' => $cnf->maxWaitTime,
                'open_http2_protocol' => $cnf->http2Enabled, 'backlog' => $cores * 30, // number of connections in queue
                'max_request' => $cnf->maxWorkerRequests, 'http_compression' => true,
                'max_conn' => $cnf->maxConnections, 'task_enable_coroutine' => true,
                'http_compression_level' => 3, 'daemonize' => $cnf->isDaemon,
                'dispatch_mode' => self::SCHEDULING[$cnf->schedulingAlgorithm],
                'websocket_compression' => true, 'ssl_allow_self_signed' => true,
                'ssl_cert_file' => $cnf->sslCert, 'ssl_key_file' => $cnf->sslKey
            ]);
            $server->addListener($cnf->ip, $cnf->portHttps, self::SOCKET_TCP | self::SSL);
            return $server;
        }

        private static function makeURI(string $scheme, string $host, array &$server): URI
        {
            $query = !empty($server['query_string']) ? '?' . $server['query_string'] : null;
            $path = $scheme . '://' . $host . $server['path_info'] . $query;
            return new URI($path);
        }

        private static function handleHTTP(string $scheme, Request &$req, ResponseWriter $writer): App
        {
            $uri = self::makeURI($scheme, $req->header['host'], $req->server);
            $request = (new RequestEntityBuilder())
                    ->protocol($req->server['server_protocol'])
                    ->method($req->server['request_method'])
                    ->headers(new HttpHeader($req->header))
                    ->time($req->server['request_time'])
                    ->files(self::getPostedFiles($req->files))
                    ->ip($req->server['remote_addr'])
                    ->rawBody($req->rawcontent())
                    ->body(self::getPostedBody($req))
                    ->cookies($req->cookie)
                    ->query($req->get)
                    ->uri($uri)
                    ->build();
            $response = new ResponseEntity($request, HttpStatus::OK, new HttpHeader());
            return new App($response, $writer);
        }

        private static function getPostedBody(Request &$req): ?array
        {
            $contentType = $req->header['content-type'] ?? null;
            if (!empty($req->post) || empty($contentType)) {
                return $req->post;
            }
            $type = MediaType::subtype(strtolower($contentType));
            return DataConvertor::convertFrom($req->rawcontent(), $type);
        }

        private static function getPostedFiles(?array $files): array
        {
            $uploaded = [];
            if (empty($files)) {
                return $uploaded;
            }
            foreach ($files as $name => $file) {
                if (!empty($file['tmp_name'])) {
                    $uploaded[$name] = new File(
                            path: $file['tmp_name'], type: $file['type'],
                            size: $file['size'], name: $file['name'],
                            error: $file['error']
                    );
                } else {
                    $uploaded[$name] = self::getPostedFiles($file);
                }
            }
            return $uploaded;
        }

        private static function checkFrameworkRequirements()
        {
            if (version_compare(Definitions::MIN_PHP_VERSION, PHP_VERSION) >= 0) {
                fwrite(STDERR, 'PHP version ' . Definitions::MIN_PHP_VERSION . ' or higher is required' . PHP_EOL);
                exit(1);
            }
            foreach (Definitions::REQUIRED_EXTENSIONS as $extension) {
                if (!extension_loaded($extension)) {
                    fwrite(STDERR, 'Please install PHP ' . $extension . ' extension' . PHP_EOL);
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
            $cnf = ServerConfig::getConfig();
            $server = self::configure($cnf);
            self::runServer($server, $cnf, $arguments);
        }

        private static function runServer(WSocket &$server, ServerConfig &$cnf, array &$arguments): void
        {
            $clients = [];
            $server->on('start', function () use (&$arguments) {
                echo 'Server started on ' . date(DATE_RSS) . PHP_EOL;
                TestCase::config($arguments);
            });
            $server->on('request', function (Request $req, Response $res) use (&$cnf) {
                $scheme = $cnf->portHttp === $req->server['server_port'] ? 'http' : 'https';
                $app = self::handleHTTP($scheme, $req, new SwooleHttpResponseWriter($res));
                $app->runApp();
            });
            $server->on('open', function (WSocket $server, Request $req) use (&$clients, &$cnf) {
                $scheme = $cnf->portHttp === $req->server['server_port'] ? 'ws' : 'wss';
                $app = self::handleHTTP($scheme, $req, new SwooleWebSocketResponseWriter($server, $req->fd));
                $clients[$req->fd] = $app;
            });
            $server->on('message', function (WSocket $server, Frame $frame) use (&$clients) {
                $app = $clients[$frame->fd] ?? null;
                $app?->request->withRawBody($frame->data);
                $app?->runApp();
            });
            $server->on('close', function (WSocket $server, int $fd) use (&$clients) {
                unset($clients[$fd]);
            });
            $server->on('task', fn() => null);
            $server->start();
        }
    }

}