<?php

namespace features\utils {

    /**
     * Class Duration
     *
     * Represents a time duration with a specific unit (seconds, minutes, hours, etc.).
     * Provides factory methods for creating durations in different units and
     * utility methods for converting them into DateTime objects or calculating
     * relative time from now.
     *
     * @author coder
     * @created Mar 12, 2025 at 11:32:07 AM
     */
    final class Duration
    {

        /**
         * The numeric value of the duration.
         *
         * @var int
         */
        public readonly int $value;

        /**
         * The unit of the duration (e.g., SECONDS, MINUTES, HOURS).
         *
         * @var string
         */
        public readonly string $unit;

        /**
         * Private constructor to enforce factory method usage.
         *
         * @param int $value The numeric value of the duration.
         * @param string $unit The unit of the duration.
         */
        private function __construct(int $value, string $unit)
        {
            $this->value = $value;
            $this->unit = $unit;
        }

        /**
         * Creates a duration in seconds.
         *
         * @param int $value Number of seconds.
         * @return Duration
         */
        public static function ofSeconds(int $value): Duration
        {
            return new Duration($value, 'SECONDS');
        }

        /**
         * Creates a duration in minutes.
         *
         * @param int $value Number of minutes.
         * @return Duration
         */
        public static function ofMinutes(int $value): Duration
        {
            return new Duration($value, 'MINUTES');
        }

        /**
         * Creates a duration in hours.
         *
         * @param int $value Number of hours.
         * @return Duration
         */
        public static function ofHours(int $value): Duration
        {
            return new Duration($value, 'HOURS');
        }

        /**
         * Creates a duration in days.
         *
         * @param int $value Number of days.
         * @return Duration
         */
        public static function ofDays(int $value): Duration
        {
            return new Duration($value, 'DAYS');
        }

        /**
         * Creates a duration in years.
         *
         * @param int $value Number of years.
         * @return Duration
         */
        public static function ofYears(int $value): Duration
        {
            return new Duration($value, 'YEARS');
        }

        /**
         * Creates a duration in months.
         *
         * @param int $value Number of months.
         * @return Duration
         */
        public static function ofMonths(int $value): Duration
        {
            return new Duration($value, 'MONTHS');
        }

        /**
         * Creates a duration in weeks.
         *
         * @param int $value Number of weeks.
         * @return Duration
         */
        public static function ofWeeks(int $value): Duration
        {
            return new Duration($value, 'WEEKS');
        }

        /**
         * Calculates the number of seconds between now and the target DateTime
         * represented by this duration.
         *
         * @return int Seconds difference from now.
         */
        public function fromNow(): int
        {
            return $this->toDateTime()->getTimestamp() - time();
        }

        /**
         * Converts the duration into a DateTimeImmutable object.
         *
         * @return \DateTimeImmutable
         */
        public function toDateTime(): \DateTimeImmutable
        {
            return new \DateTimeImmutable('+' . $this->value . ' ' . $this->unit);
        }
    }

}
