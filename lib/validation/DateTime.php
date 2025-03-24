<?php

/**
 * Description of DateTime
 * @author coder
 *
 * Created on: Aug 31, 2020 at 9:06:50 PM
 */

namespace lib\validation {

    final class DateTime {

        public static function dateTime(?string $str): ?string {
            return preg_match('/^[0-9]{4}(\-[0-9]{2}){2}[ ]([01][0-9]|[2][0-3])([:][0-5][0-9]){1,2}$/', $str) ? null : 'is not a valid date time';
        }

        public static function time(?string $time): ?string {
            return preg_match('/^([01][0-9]|[2][0-3])([:][0-5][0-9]){1,2}$/', $time) ? null : 'is not a valid time';
        }

        public static function date(?string $date): ?string {
            return preg_match('/^[0-9]{4}(\-[0-9]{2}){2}$/', $date) ? null : 'is not a valid date';
        }

        public static function minDate(?string $str, string $mindate): ?string {
            return strtotime($str) >= strtotime($mindate) ? null : 'is below minimum date';
        }

        public static function maxDate(?string $str, string $maxdate): ?string {
            return strtotime($str) <= strtotime($maxdate) ? null : 'is above maximum date';
        }

        public static function dateRange(?string $date, string $mindate, string $maxdate): ?string {
            $str = strtotime($date);
            return ($str >= strtotime($mindate) && $str <= strtotime($maxdate)) ? null : 'is out of range';
        }
    }

}
