<?php

set_include_path(get_include_path() . PATH_SEPARATOR . 'vendor');
spl_autoload_register(function (string $class) {
    require_once str_replace('\\', '/', $class) . '.php';
});

/**
 * Server root directory
 */
define('SHANI_SERVER_ROOT', __DIR__);
/**
 * Current timestamp according to RFC3339
 */
define('SHANI_CURRENT_TIMESTAMP', date(DATE_RFC3339));
