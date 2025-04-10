<?php

/**
 * Description of DateTime
 * @author coder
 *
 * Created on: Aug 31, 2020 at 9:06:50 PM
 */

namespace lib\validation {

    final class DateTime
    {

        public static function isDateTime(?string $str): bool
        {
            return preg_match('/^[0-9]{4}(\-[0-9]{2}){2}[ ]([01][0-9]|[2][0-3])([:][0-5][0-9]){1,2}$/', $str) === 1;
        }

        public static function isTime(?string $time): bool
        {
            return preg_match('/^([01][0-9]|[2][0-3])([:][0-5][0-9]){1,2}$/', $time) === 1;
        }

        public static function isDate(?string $date): bool
        {
            return preg_match('/^[0-9]{4}(\-[0-9]{2}){2}$/', $date) === 1;
        }

        public static function minDate(?string $str, string $mindate): bool
        {
            return strtotime($str) >= strtotime($mindate);
        }

        public static function maxDate(?string $str, string $maxdate): bool
        {
            return strtotime($str) <= strtotime($maxdate);
        }

        public static function dateRange(?string $date, string $mindate, string $maxdate): bool
        {
            $str = strtotime($date);
            return $str >= strtotime($mindate) && $str <= strtotime($maxdate);
        }
    }

}
