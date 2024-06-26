<?php

/**
 * Description of HttpServer
 * @author coder
 *
 * Created on: Mar 6, 2024 at 3:42:29 PM
 */

namespace shani\server\swoole {

    use shani\engine\http\Host;
    use shani\engine\http\App;
    use Swoole\WebSocket\Server as WSocket;

    final class HttpServer
    {

        private const SOCKET_TCP = 1, SSL = 512;
        private const SCHEDULING = ['ROUND_ROBIN' => 1, 'PREEMPTIVE' => 3, 'FIXED' => 2];

        private static function configure(array $cnf): WSocket
        {
            ini_set('display_errors', $cnf['SHOW_ERRORS']);
            $maxCon = (int) $cnf['MAX_CONNECTIONS'];
            new \library\Concurrency(new Concurrency());
            \library\Event::setHandler(new Event());
            \library\Mime::setHandler(new Cache(1500, 100));
            \shani\engine\http\Session::setHandler(new Cache($maxCon, 1000));
            $server = new WSocket($cnf['IP'], $cnf['PORTS']['HTTP']);
            $cores = swoole_cpu_num();
            $server->set([
                'task_worker_num' => $cores, 'reactor_num' => $cores,
                'worker_num' => $cores, 'enable_coroutine' => true,
                'open_http2_protocol' => $cnf['ENABLE_HTTP2'],
                'backlog' => $cores * 30, // number of connections in queue
                'max_request' => (int) $cnf['REQ_PER_WORKER'], 'http_compression' => true,
                'max_conn' => $maxCon, 'task_enable_coroutine' => true,
                'http_compression_level' => 3, 'daemonize' => $cnf['RUNAS_DAEMON'],
                'dispatch_mode' => self::SCHEDULING[$cnf['SCHEDULING_ALGORITHM']],
                'websocket_compression' => true, 'ssl_allow_self_signed' => true,
                'ssl_cert_file' => str_replace('${SSL_DIR}', \shani\ServerConfig::SSL_DIR, $cnf['SSL']['CERT']),
                'ssl_key_file' => str_replace('${SSL_DIR}', \shani\ServerConfig::SSL_DIR, $cnf['SSL']['KEY'])
            ]);
            $server->addListener($cnf['IP'], $cnf['PORTS']['HTTPS'], self::SOCKET_TCP | self::SSL);
            return $server;
        }

        private static function makeURI(string $scheme, string $host, array &$server): \library\URI
        {
            $query = !empty($server['query_string']) ? '?' . $server['query_string'] : null;
            $path = $scheme . '://' . $host . $server['path_info'] . $query;
            return new \library\URI($path);
        }

        private static function handleHTTP(string $scheme, \Swoole\Http\Request &$req, \Swoole\Http\Response &$res)
        {
            $uri = self::makeURI($scheme, $req->header['host'], $req->server);
            new App(new Request($req, $uri), new Response($res), new Host($uri->hostname()));
        }

        public static function start(): void
        {
            if (!extension_loaded('swoole')) {
                exit('Please install PHP swoole extension' . PHP_EOL);
            }
            \library\Utils::errorHandler();
            $cnf = \shani\ServerConfig::server();
            $server = self::configure($cnf);
            $server->on('start', function () use (&$cnf) {
                echo 'http://' . $cnf['IP'] . ':' . $cnf['PORTS']['HTTP'] . PHP_EOL;
                echo 'https://' . $cnf['IP'] . ':' . $cnf['PORTS']['HTTPS'] . PHP_EOL;
            });
            $server->on('request', function (\Swoole\Http\Request $req, \Swoole\Http\Response $res) use (&$cnf) {
                $scheme = $cnf['PORTS']['HTTP'] === $req->server['server_port'] ? 'http' : 'https';
                self::handleHTTP($scheme, $req, $res);
            });
            $requests = [];
            $server->on('open', function (WSocket $server, \Swoole\Http\Request $req)use (&$requests) {
//                $requests[$req->fd] = $req;
            });
            $server->on('message', function (WSocket $server, \Swoole\WebSocket\Frame $frame)use (&$requests) {
//                print_r($requests[$frame->fd]);
            });
            $server->on('close', function (WSocket $server, int $fd)use (&$requests) {
//                unset($requests[$fd]);
            });
            $server->on('task', fn() => null);
            $server->start();
        }
    }

}