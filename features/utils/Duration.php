<?php

/**
 * Description of Duration
 * @author coder
 *
 * Created on: Mar 12, 2025 at 11:32:07 AM
 */

namespace features\utils {

    final class Duration
    {

        public readonly int $value;
        public readonly DurationUnit $unit;

        private function __construct(int $value, DurationUnit $unit)
        {
            $this->value = $value;
            $this->unit = $unit;
        }

        /**
         * Create a Duration object
         * @param int $value Duration value
         * @param DurationUnit $unit Duration unit
         * @return Duration
         */
        public static function of(int $value, DurationUnit $unit): Duration
        {
            return new Duration($value, $unit);
        }

        /**
         * return number of seconds from now
         * @return int
         */
        public function fromNow(): int
        {
            return $this->toDateTime() - time();
        }

        /**
         * Convert Duration object into DatetimeInterface
         * @return \DateTimeImmutable
         */
        public function toDateTime(): \DateTimeImmutable
        {
            return new \DateTimeImmutable($this->value . ' ' . $this->unit->name);
        }
    }

}
