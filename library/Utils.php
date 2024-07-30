<?php

/**
 * Description of Utils
 * @author coder
 *
 * Created on: Feb 13, 2024 at 4:56:01 PM
 */

namespace library {


    final class Utils
    {

        public static function digest(string $str, string $algorithm = 'crc32b'): string
        {
            return hash($algorithm, $str);
        }

        public static function errorHandler()
        {
            set_error_handler(function (int $errno, string $errstr, string $errfile, int $errline) {
                throw new \ErrorException($errstr, 0, $errno, $errfile, $errline);
            });
        }

        public static function kebab2camelCase(string $str, string $separator = '-'): string
        {
            if (str_contains($str, $separator)) {
                $dot = strpos($str, '.');
                if ($dot > 0) {
                    $str = substr($str, 0, $dot);
                }
//                $str = preg_replace_callback('/(?<=-)[a-z]/', fn($ch) => strtoupper($ch[0]), $str);
                $str = lcfirst(ucwords($str, $separator));
                return str_replace($separator, '', $str);
            }
            return $str;
        }
    }

}
