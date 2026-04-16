<?php

use shani\launcher\ApplicationLauncher;
use shani\launcher\Framework;

/**
 * Server root directory
 */
define('SHANI_SERVER_ROOT', __DIR__);
/**
 * Current timestamp according to RFC3339
 */
define('SHANI_CURRENT_TIMESTAMP', date(DATE_RFC3339));
set_include_path(get_include_path() . PATH_SEPARATOR . 'vendor');
spl_autoload_register(function (string $class) {
    require_once str_replace('\\', '/', $class) . '.php';
});
$config = new Framework();

if (PHP_SAPI === 'cli') {
    /**
     * ***********************************************************************
     * Run the application using Swoole server. It requires swoole extension *
     * to be installed, and of course UNIX family operating system           *
     * ***********************************************************************
     */
    ApplicationLauncher::start(new \shani\servers\swoole\SwooleServer($config));
} else {
    /**
     * **************************************************************
     * Run the application using any CGI server e.g apache or nginx *
     * **************************************************************
     */
    ApplicationLauncher::start(new shani\servers\cgi\CgiServer($config));
}
