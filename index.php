<?php

declare(strict_types=1);
define('SERVER_ROOT', __DIR__);
set_include_path(get_include_path() . PATH_SEPARATOR . 'library');
spl_autoload_register(function (string $class) {
    require_once str_replace('\\', '/', $class) . '.php';
});
shani\server\swoole\HttpServer::start();
