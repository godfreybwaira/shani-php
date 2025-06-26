<?php

declare(strict_types=1);

use shani\FrameworkConfig;
use shani\WebServer;

/**
 * Server root directory
 */
define('SERVER_ROOT', __DIR__);
//set_include_path(get_include_path() . PATH_SEPARATOR . 'vendor');
spl_autoload_register(function (string $class) {
    require_once str_replace('\\', '/', $class) . '.php';
});
$config = new FrameworkConfig();

if (PHP_SAPI === 'cli') {
    /*     * *****************************************************************
     * Run the application using Swoole server. It requires swoole extension *
     * to be installed                                                       *
     * ***********************************************************************
     */

    /*
     * Testing env
     */
    //WebServer::start(new \shani\servers\swoole\SwooleServer($config), $argv);

    /**
     * Production env
     */
    WebServer::start(new \shani\servers\swoole\SwooleServer($config));
} else {
    /*     * ********************************************************
     * Run the application using any CGI server e.g apache or nginx *
     * **************************************************************
     */

    /**
     * Testing env
     */
    //WebServer::start(new shani\servers\cgi\CgiServer($config), [
    //    'host' => 'localhost', 'env' => 'TEST'
    //]);

    /**
     * Production env
     */
    WebServer::start(new shani\servers\cgi\CgiServer($config));
}
