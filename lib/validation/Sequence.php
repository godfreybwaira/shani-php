<?php

/**
 * Description of Sequence
 * @author coder
 *
 * Created on: Aug 31, 2020 at 8:20:08 PM
 */

namespace lib\validation {

    final class Sequence {

        public static function url($str): ?string {
            return filter_var($str, FILTER_VALIDATE_URL) !== false ? null : 'is not a valid url';
        }

        public static function email($str): ?string {
            return filter_var($str, FILTER_VALIDATE_EMAIL) !== false ? null : 'is not a valid email';
        }

        public static function emails($str, string $sep = ','): ?string {
            $emails = explode($sep, $str);
            foreach ($emails as $email) {
                if (filter_var(trim($email), FILTER_VALIDATE_EMAIL) === false) {
                    return 'contains invalid email';
                }
            }
            return null;
        }

        private static function length($str, int $length): ?string {
            return strlen($str) !== $length ? 'is too short' : null;
        }

        public static function word($str, int $length = null): ?string {
            if ($length === null) {
                return ctype_alpha($str) ? null : 'is not a word';
            }
            return self::length($str, $length);
        }

        public static function sentence($str, string $sep = ' ', int $length = null): ?string {
            if ($length === null) {
                return preg_match('/^[a-z]+([' . $sep . '][a-z]+)+$/i', $str) ? null : 'is not a sentence';
            }
            return self::length($str, $length);
        }

        public static function string($str, ?string $sep = ' ', int $length = null): ?string {
            if ($length === null) {
                return preg_match('/^\w+([' . $sep . ']\w+)*$/i', $str) ? null : 'is not a valid string';
            }
            return self::length($str, $length);
        }

        public static function in($str, string $list, string $sep = ','): ?string {
            return in_array($str, explode($sep, $list)) ? null : 'is not in the list';
        }

        public static function lowercase($str, int $length = null): ?string {
            if ($length === null) {
                return ctype_lower($str) ? null : 'must be lower case';
            }
            return self::length($str, $length);
        }

        public static function uppercase($str, int $length = null): ?string {
            if ($length === null) {
                return ctype_upper($str) ? null : 'must be upper case';
            }
            return self::length($str, $length);
        }

        public static function filled($str): ?string {
            return !(empty($str) || ctype_space($str)) ? null : 'is empty';
        }

        public static function phone($str, int $length = null): ?string {
            if ($length === null) {
                return preg_match('/^([+]{1}[0-9]{12,13}|0[0-9]{9})$/', $str) ? null : 'is not valid';
            }
            return self::length($str, $length);
        }

        public static function name($str, int $length = null): ?string {
            if ($length === null) {
                return preg_match('/^([a-z]+([\'][a-z]+)*){2,}$/i', $str) ? null : 'is not a valid name';
            }
            return self::length($str, $length);
        }

    }

}
