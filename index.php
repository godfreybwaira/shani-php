<?php

use shani\launcher\ApplicationLauncher;
use shani\launcher\Framework;

require_once 'classloader.php';

$framework = new Framework();

if (PHP_SAPI === 'cli') {
    /**
     * ***********************************************************************
     * Run the application using Swoole server. It requires swoole extension *
     * to be installed, and of course UNIX family operating system           *
     * ***********************************************************************
     */
    ApplicationLauncher::start(new \shani\servers\swoole\SwooleServer($framework));
} else {
    /**
     * **************************************************************
     * Run the application using any CGI server e.g apache or nginx *
     * **************************************************************
     */
    ApplicationLauncher::start(new shani\servers\cgi\CgiServer($framework));
}
