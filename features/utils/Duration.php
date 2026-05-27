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
    final class Duration implements \Stringable
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

        /**
         * Adds another Duration to this one.
         *
         * @param Duration $other The other duration to add.
         * @return Duration A new Duration representing the sum of both durations.
         */
        public function add(Duration $other): Duration
        {
            $totalSeconds = $this->fromNow() + $other->fromNow();
            return Duration::ofSeconds($totalSeconds);
        }

        /**
         * Subtracts another Duration from this one.
         *
         * @param Duration $other The other duration to subtract.
         * @return Duration A new Duration representing the difference (never negative).
         *
         * @example
         * Duration::ofMinutes(5)->subtract(Duration::ofMinutes(2)); // 180 seconds
         */
        public function subtract(Duration $other): Duration
        {
            $totalSeconds = $this->fromNow() - $other->fromNow();
            return Duration::ofSeconds($totalSeconds);
        }

        /**
         * Returns a human-readable string representation of the duration.
         *
         * @return string A string like "5 MINUTES" or "2 HOURS".
         *
         */
        #[\Override]
        public function __toString(): string
        {
            return $this->value . ' ' . $this->unit;
        }

        /**
         * Checks if this duration is equal to another.
         *
         * @param Duration $other The duration to compare against.
         * @return bool True if they are equal, false otherwise.
         */
        public function equals(Duration $other): bool
        {
            return $this->fromNow() === $other->fromNow();
        }

        /**
         * Checks if this duration is longer than another.
         *
         * @param Duration $other The duration to compare against.
         * @return bool True if this duration is longer, false otherwise.
         */
        public function isLongerThan(Duration $other): bool
        {
            return $this->fromNow() > $other->fromNow();
        }

        /**
         * Checks if this duration is shorter than another.
         *
         * @param Duration $other The duration to compare against.
         * @return bool True if this duration is shorter, false otherwise.
         */
        public function isShorterThan(Duration $other): bool
        {
            return $this->fromNow() < $other->fromNow();
        }

        /**
         * Check if the given timestamp is behind the current time (expired)
         * @param int $timestamp The timestamp to check
         * @return bool True if expired, false otherwise
         */
        public static function expired(int $timestamp): bool
        {
            return $timestamp < time();
        }

        /**
         * Create duration object from timestamp
         * @param int $timestamp Timestamp to compute from
         * @return Duration Duration object
         */
        public static function fromTimestamp(int $timestamp): Duration
        {
            return Duration::ofSeconds($timestamp - time());
        }
    }

}
