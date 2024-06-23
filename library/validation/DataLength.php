<?php

/**
 * Description of DataLength
 * @author coder
 *
 * Created on: Aug 31, 2020 at 10:43:47 PM
 */

namespace library\validation {

    final class DataLength {

        public static function min($str, int $minlength): ?string {
            return strlen($str) >= $minlength ? null : 'is too short';
        }

        public static function max($str, int $maxlength): ?string {
            return strlen($str) <= $maxlength ? null : 'is too long';
        }

        public static function equal($str, int $length): ?string {
            return strlen($str) === $length ? null : 'is not of equal length';
        }

        public static function between($str, int $minlength, int $maxlength): ?string {
            $length = strlen($str);
            return ($length >= $minlength && $length <= $maxlength) ? null : 'is not within range';
        }
    }

}
