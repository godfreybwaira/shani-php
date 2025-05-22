<?php

/**
 * Shani HTTP server powered by swoole
 * @author coder
 *
 * Created on: Mar 6, 2024 at 3:42:29 PM
 */

namespace shani\servers\swoole {

    use lib\DataConvertor;
    use lib\File;
    use lib\http\RequestEntity;
    use lib\MediaType;
    use lib\RequestEntityBuilder;
    use shani\contracts\ConcurrencyInterface;
    use shani\contracts\EventHandler;
    use shani\contracts\SupportedWebServer;
    use shani\FrameworkConfig;
    use Swoole\Http\Request;
    use Swoole\Http\Response;
    use Swoole\WebSocket\Frame;
    use Swoole\WebSocket\Server;

    final class SwooleServer implements SupportedWebServer
    {

        private const SOCKET_TCP = 1, SSL = 512;
        private const SCHEDULING = ['ROUND_ROBIN' => 1, 'FIXED' => 2, 'PREEMPTIVE' => 3, 'IPMOD' => 4];

        private readonly Server $server;
        private readonly FrameworkConfig $config;
        private array $clients = [];

        public function __construct(FrameworkConfig $config)
        {
            self::CheckRequirements();
            $swoole = yaml_parse_file(__DIR__ . '/config.yml');
            $this->server = new Server($config->serverIp, $config->httpPort);
            $this->config = $config;
            $cores = swoole_cpu_num();
            $this->server->set([
                'package_max_length' => $config->payloadSize,
                'task_worker_num' => $cores, 'reactor_num' => $cores,
                'worker_num' => $cores, 'enable_coroutine' => true,
                'reload_async' => true, 'max_wait_time' => $swoole['MAX_WAIT_TIME'],
                'open_http2_protocol' => $swoole['ENABLE_HTTP2'], 'backlog' => $cores * 30, // number of connections in queue
                'max_request' => $swoole['MAX_WORKER_REQUESTS'], 'http_compression' => true,
                'max_conn' => $swoole['MAX_CONNECTIONS'], 'task_enable_coroutine' => true,
                'http_compression_level' => 3, 'daemonize' => $swoole['RUNAS_DAEMON'],
                'dispatch_mode' => self::SCHEDULING[$swoole['SCHEDULING_ALGORITHM']],
                'websocket_compression' => true, 'ssl_allow_self_signed' => true,
                'ssl_cert_file' => $config->sslCert, 'ssl_key_file' => $config->sslKey
            ]);
            $this->server->addListener($config->serverIp, $config->httpsPort, self::SOCKET_TCP | self::SSL);
        }

        public static function CheckRequirements(): void
        {
            if (!extension_loaded('swoole')) {
                fwrite(STDERR, 'Please install PHP swoole extension' . PHP_EOL);
                exit(1);
            }
        }

        public function start(callable $callback): void
        {
            $this->server->on('start', fn() => $callback());
            $this->server->on('open', function (Server $server, Request $req) {
                $scheme = $this->config->httpPort === $req->server['server_port'] ? 'ws' : 'wss';
                $this->clients[$req->fd] = self::createRequest($scheme, $req);
            });
            $this->server->on('close', function (Server $server, int $fd) {
                unset($this->clients[$fd]);
            });
            $this->server->on('task', fn() => null);
            $this->server->start();
        }

        public function request(callable $callback): self
        {
            $this->server->on('request', function (Request $req, Response $res) use (&$callback) {
                $scheme = $this->config->httpPort === $req->server['server_port'] ? 'http' : 'https';
                $request = self::createRequest($scheme, $req);
                $writer = new SwooleHttpResponseWriter($res);
                $callback($request, $writer);
            });
            $this->server->on('message', function (Server $server, Frame $frame) use (&$callback) {
                $request = $this->clients[$frame->fd] ?? null;
                if (!empty($request)) {
                    $request->withRawBody($frame->data);
                    $writer = new SwooleWebSocketResponseWriter($server, $frame->fd);
                    $callback($request, $writer);
                }
            });
            return $this;
        }

        public function stop(): void
        {
            $this->server->shutdown();
        }

        private static function createRequest(string $scheme, Request &$req): RequestEntity
        {
            $query = !empty($req->server['query_string']) ? '?' . $req->server['query_string'] : null;
            $path = $scheme . '://' . $req->header['host'] . $req->server['path_info'] . $query;
            $request = (new RequestEntityBuilder())
                    ->protocol($req->server['server_protocol'])
                    ->files(self::getPostedFiles($req->files))
                    ->method($req->server['request_method'])
                    ->time($req->server['request_time'])
                    ->ip($req->server['remote_addr'])
                    ->body(self::getPostedBody($req))
                    ->rawBody($req->rawcontent())
                    ->headers($req->header)
                    ->cookies($req->cookie)
                    ->query($req->get)
                    ->uri($path)
                    ->build();
            return $request;
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

        public function getConcurrencyHandler(): ConcurrencyInterface
        {
            return new SwooleConcurrency();
        }

        public function getEventHandler(): EventHandler
        {
            return new SwooleEvent();
        }
    }

}