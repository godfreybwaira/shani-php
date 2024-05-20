<?php

/**
 * Description of Logger
 * @author coder
 *
 * Created on: May 21, 2024 at 12:01:27 AM
 */

namespace library {

    final class Logger
    {

        public static function error(\Exception $e, string $destination): void
        {
            Concurrency::async(function ()use (&$destination, &$e) {
                $dst = $destination . '/errors';
                if (is_dir($dst) || mkdir($dst, 0744, true)) {
                    $dst .= $filename = '/error-' . date('Y-m-d') . '.log';
                    $file = fopen($dst, 'a');
                    $str = 'Message: ' . $e->getMessage() . PHP_EOL;
                    $str .= 'Source: ' . $e->getFile() . PHP_EOL;
                    $str .= 'Line: ' . $e->getLine() . PHP_EOL;
                    $str .= '***' . PHP_EOL;
                    fwrite($file, $str);
                    fclose($file);
                }
            });
        }
    }

}
