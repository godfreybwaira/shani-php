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
    use lib\http\HttpHeader;
    use lib\http\HttpStatus;
    use lib\http\ResponseEntity;
    use lib\MediaType;
    use lib\RequestEntityBuilder;
    use lib\URI;
    use lib\Utils;
    use shani\core\Definitions;
    use shani\http\App;
    use shani\http\UploadedFile;
    use shani\ServerConfig;
    use Swoole\Http\Request;
    use Swoole\Http\Response;
    use Swoole\WebSocket\Frame;
    use Swoole\WebSocket\Server as WSocket;

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

        private static function handleHTTP(string $scheme, Request &$req, Response &$res)
        {
            $uri = self::makeURI($scheme, $req->header['host'], $req->server);
            $request = (new RequestEntityBuilder())
                    ->protocol($req->server['server_protocol'])
                    ->method($req->server['request_method'])
                    ->headers(new HttpHeader($req->header))
                    ->time($req->server['request_time'])
                    ->files(self::getFiles($req->files))
                    ->ip($req->server['remote_addr'])
                    ->body(self::getBody($req))
                    ->cookies($req->cookie)
                    ->query($req->get)
                    ->uri($uri)
                    ->build();
            $response = new ResponseEntity($request, HttpStatus::OK, new HttpHeader());
            new App($response, new SwooleResponseWriter($res));
        }

        private static function getBody(Request &$req): ?array
        {
            $contentType = $req->header['content-type'] ?? null;
            if (!empty($req->post) || empty($contentType)) {
                return $req->post;
            }
            $type = MediaType::explode(strtolower($contentType))[1];
            return DataConvertor::convertFrom($req->rawcontent(), $type);
        }

        private static function getFiles(?array $files): array
        {
            $uploaded = [];
            if (empty($files)) {
                return $uploaded;
            }
            foreach ($files as $name => $file) {
                if (!empty($file['tmp_name'])) {
                    $uploaded[$name] = new UploadedFile(
                            path: $file['tmp_name'], type: $file['type'],
                            size: $file['size'], name: $file['name'],
                            error: $file['error']
                    );
                } else {
                    $uploaded[$name] = self::getFiles($file);
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
            $cnf = ServerConfig::getConfig();
            $server = self::configure($cnf);
            $server->on('start', function () {
                echo 'Server started on ' . date(DATE_RSS) . PHP_EOL;
            });
            $server->on('request', function (Request $req, Response $res) use (&$cnf) {
                $scheme = $cnf->portHttp === $req->server['server_port'] ? 'http' : 'https';
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