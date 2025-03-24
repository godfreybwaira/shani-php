<?php

/**
 * Description of Password
 * @author coder
 *
 * Created on: Jan 28, 2021 at 2:20:20 PM
 */

namespace lib\validation {

    final class Password implements PasswordComplexity {

        public static function validate($str): ?string {
            $msg = 'must contains letters (upper case and lower case), number and/or symbols, all ' . self::MIN_LENGTH . ' or more';
            $expression = null;
            if (self::DIGITS) {
                $expression .= '(?=.*[0-9])';
            }
            if (!self::LETTERS) {
                if (self::LOWER_CASE) {
                    $expression .= '(?=.*[a-z])';
                }
                if (self::UPPER_CASE) {
                    $expression .= '(?=.*[A-Z])';
                }
            } else {
                $expression .= '(?=.*[A-Za-z])';
            }
            if (self::SYMBOLS) {
                $expression .= '(?=.*[)!@#$%^&*,+=(?_])';
            }
            return preg_match('/^' . $expression . '.{' . self::MIN_LENGTH . ',' . self::MAX_LENGTH . '}$/', $str) ? null : $msg;
        }

        public static function digits($str, int $minLength = 1, int $maxLength = null): ?string {
            $msg = 'must contain digits';
            return preg_match('/[0-9]{' . $minLength . ',' . $maxLength . '}/', $str) ? null : $msg;
        }

        public static function lowercase($str, int $minLength = 1, int $maxLength = null): ?string {
            $msg = 'must contains lower case letters';
            return preg_match('/[a-z]{' . $minLength . ',' . $maxLength . '}/', $str) ? null : $msg;
        }

        public static function uppercase($str, int $minLength = 1, int $maxLength = null): ?string {
            $msg = 'must contains upper case letters';
            return preg_match('/[A-Z]{' . $minLength . ',' . $maxLength . '}/', $str) ? null : $msg;
        }

        public static function symbols($str, int $minLength = 1, int $maxLength = null): ?string {
            $msg = 'must contains symbols such as )!@#$%^&*,+=(?_';
            return preg_match('/[\)!@#$%^&*,+=\(?_]{' . $minLength . ',' . $maxLength . '}/', $str) ? null : $msg;
        }
    }

}
