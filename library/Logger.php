<?php

/**
 * Description of Logger
 * @author coder
 *
 * Created on: May 21, 2024 at 12:01:27 AM
 */

namespace library {

    use shani\engine\http\App;

    final class Logger
    {

        public static function logError(App &$app, int $errno, string $errstr, string $errfile, int $errline): void
        {
            $content = 'Time: ' . date('H:i:s') . PHP_EOL;
            $content .= 'Code: ' . $errno . PHP_EOL;
            $content .= 'Message: ' . $errstr . PHP_EOL;
            $content .= 'Source: ' . $errfile . PHP_EOL;
            $content .= 'Line: ' . $errline . PHP_EOL . PHP_EOL;
            self::writer($app->asset()->private('/errors'), $content, '/error-');
        }

        public static function logException(App &$app, \Exception &$e): void
        {
            self::logError($app, $e->getCode(), $e->getMessage(), $e->getFile(), $e->getLine());
        }

        private static function writer(string $destination, string $content, string $prefix): void
        {
            Concurrency::async(function () use (&$destination, &$content, &$prefix) {
                if (is_dir($destination) || mkdir($destination, 0744, true)) {
                    $filename = $destination . $prefix . date('Y-m-d') . '.log';
                    $file = fopen($filename, 'a');
                    fwrite($file, $content);
                    fclose($file);
                    echo 'Error has occured, please check logs' . PHP_EOL;
                }
            });
        }
    }

}
