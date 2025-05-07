<?php

declare(strict_types=1);

use shani\FrameworkConfig;
use shani\HttpServer;
use shani\servers\swoole\SwooleServer;

/**
 * Server root directory
 */
define('SERVER_ROOT', __DIR__);
//set_include_path(get_include_path() . PATH_SEPARATOR . 'library');
spl_autoload_register(function (string $class) {
    require_once str_replace('\\', '/', $class) . '.php';
});
$config = new FrameworkConfig();
HttpServer::start(new SwooleServer($config), $argv);
