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

        public const BUFFER_SIZE_1MB = 1048576;
        private const MIN_TO_SEC = 60, DAY_TO_HOURS = 24, SEC_TO_HOURS = 3600,
                MONTH_TO_DAYS = 30, WEEK_TO_DAYS = 7, DAYS_TO_YEARS = 365;

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
            if (strpos($str, $separator) !== false) {
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

        public static function str2seconds(string $time): int
        {
            $offset = strlen($time) - 1;
            $unit = strtolower($time[$offset]);
            $duration = (int) substr($time, 0, $offset);
            if (strpos('s,i,h,d,w,m,y', $unit) === false) {
                $unit = 's';
            }
            switch ($unit) {
                case 'i':
                    return $duration * self::MIN_TO_SEC;
                case 'h':
                    return $duration * self::SEC_TO_HOURS;
                case 'd':
                    return $duration * self::SEC_TO_HOURS * self::DAY_TO_HOURS;
                case 'w':
                    return $duration * self::SEC_TO_HOURS * self::DAY_TO_HOURS * self::WEEK_TO_DAYS;
                case 'm':
                    return $duration * self::SEC_TO_HOURS * self::DAY_TO_HOURS * self::MONTH_TO_DAYS;
                case 'y':
                    return $duration * self::SEC_TO_HOURS * self::DAY_TO_HOURS * self::DAYS_TO_YEARS;
                default :
                    return $duration;
            }
        }
    }

}
