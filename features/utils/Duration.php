<?php

/**
 * Description of Duration
 * @author coder
 *
 * Created on: Mar 12, 2025 at 11:32:07 AM
 */

namespace features\utils {

    enum Duration
    {

        case SECONDS;
        case MINUTES;
        case HOURS;
        case DAYS;
        case WEEKS;
        case MONTHS;
        case YEARS;

        /**
         * Convert Duration object into DatetimeInterface
         * @param int $value Duration value
         * @param Duration $duration Duration unit
         * @return \DateTimeInterface
         */
        public static function of(int $value, Duration $duration): \DateTimeInterface
        {
            return new \DateTimeImmutable($value . ' ' . $duration->name);
        }
    }

}
