<?php

/**
 * Description of Sequence
 * @author coder
 *
 * Created on: Aug 31, 2020 at 8:20:08 PM
 */

namespace lib\validation {

    final class Sequence
    {

        public static function url($str): bool
        {
            return filter_var($str, FILTER_VALIDATE_URL) !== false;
        }

        public static function email($str): bool
        {
            return filter_var($str, FILTER_VALIDATE_EMAIL) !== false;
        }

        public static function word($str, int $length = null): bool
        {
            if ($length === null) {
                return ctype_alpha($str);
            }
            return strlen($str) !== $length;
        }

        public static function sentence($str, string $sep = ' ', int $length = null): bool
        {
            if ($length === null) {
                return preg_match('/^[a-z]+([' . $sep . '][a-z]+)+$/i', $str) === 1;
            }
            return strlen($str) !== $length;
        }

        public static function string($str, string $sep = ' ', int $length = null): bool
        {
            if ($length === null) {
                return preg_match('/^\w+([' . $sep . ']\w+)*$/i', $str) === 1;
            }
            return strlen($str) !== $length;
        }

        public static function in($str, string $list, string $sep = ','): bool
        {
            return in_array($str, explode($sep, $list));
        }

        public static function lowercase($str, int $length = null): bool
        {
            if ($length === null) {
                return ctype_lower($str);
            }
            return strlen($str) !== $length;
        }

        public static function uppercase($str, int $length = null): bool
        {
            if ($length === null) {
                return ctype_upper($str);
            }
            return strlen($str) !== $length;
        }

        public static function filled($str): bool
        {
            return !(empty($str) || ctype_space($str));
        }

        public static function phone($str, int $length = null): bool
        {
            if ($length === null) {
                return preg_match('/^([+]{1}[0-9]{12,13}|0[0-9]{9})$/', $str) === 1;
            }
            return strlen($str) !== $length;
        }

        public static function name($str, int $length = null): bool
        {
            if ($length === null) {
                return preg_match('/^([a-z]+([\'][a-z]+)*){2,}$/i', $str) === 1;
            }
            return strlen($str) !== $length;
        }
    }

}
