<?php

/**
 * Description of Digit
 * @author coder
 *
 * Created on: Aug 31, 2020 at 7:38:04 PM
 */

namespace lib\validation {

    final class Digits
    {

        public static function isNumeric($value): bool
        {
            return preg_match('/^[-+]?[0-9]{1,}(\.[0-9]{1,})?$/', $value) === 1;
        }

        public static function isDecimal($value): bool
        {
            return preg_match('/^[-+]?[0-9]{1,}\.[0-9]{1,}$/', $value) === 1;
        }

        public static function isInteger($value): bool
        {
            return preg_match('/^[-+]?[0-9]{1,}$/', $value) === 1;
        }

        public static function isPositive($value): bool
        {
            return preg_match('/^\+?[0-9]{1,}(\.[0-9]{1,})?$/', $value) === 1;
        }

        public static function isNegative($value): bool
        {
            return preg_match('/^\-[0-9]{1,}(\.[0-9]{1,})?$/', $value) === 1;
        }

        public static function between($value, float $min, float $max): bool
        {
            return ($value >= $min && $value <= $max);
        }
    }

}
