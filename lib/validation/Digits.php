<?php

/**
 * Description of Digit
 * @author coder
 *
 * Created on: Aug 31, 2020 at 7:38:04 PM
 */

namespace lib\validation {

    final class Digits {

        public static function numeric($value): ?string {
            return preg_match('/^[-+]?[0-9]{1,}(\.[0-9]{1,})?$/', $value) ? null : 'is not a valid number';
        }

        public static function decimal($value): ?string {
            return preg_match('/^[-+]?[0-9]{1,}\.[0-9]{1,}$/', $value) ? null : 'is not a decimal number';
        }

        public static function integer($value): ?string {
            return preg_match('/^[-+]?[0-9]{1,}$/', $value) ? null : 'is not an integer number';
        }

        public static function positive($value): ?string {
            return preg_match('/^\+?[0-9]{1,}(\.[0-9]{1,})?$/', $value) ? null : 'is not a positive number';
        }

        public static function negative($value): ?string {
            return preg_match('/^\-[0-9]{1,}(\.[0-9]{1,})?$/', $value) ? null : 'is not a negative number';
        }

        public static function min($value, float $min): ?string {
            return $value >= $min ? null : 'is too small';
        }

        public static function max($value, float $max): ?string {
            return $value <= $max ? null : 'is too large';
        }

        public static function between($value, float $min, float $max): ?string {
            return ($value >= $min && $value <= $max) ? null : 'is not within range';
        }
    }

}
