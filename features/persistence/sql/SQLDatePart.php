<?php

/**
 * Description of SQLDatePart
 * @author goddy
 *
 * @since v1.0: Jun 8, 2026 at 11:44:26 AM
 */

namespace features\persistence\sql {

    use features\persistence\DBDatePartInterface;

    final class SQLDatePart implements DBDatePartInterface
    {

        private readonly string $unit;
        private readonly string $columnName;

        private function __construct(string $unit, string $columnName)
        {
            $this->unit = $unit;
            $this->columnName = $columnName;
        }

        public function __toString(): string
        {
            return 'EXTRACT(' . $this->unit . ' FROM ' . $this->columnName . ')';
        }

        public function getColumnName(): string
        {
            return $this->columnName;
        }

        public static function getMonth(string $dateColumn): DBDatePartInterface
        {
            return new SQLDatePart('MONTH', $dateColumn);
        }

        public static function getQuarter(string $dateColumn): DBDatePartInterface
        {
            return new SQLDatePart('QUARTER', $dateColumn);
        }

        public static function getWeek(string $dateColumn): DBDatePartInterface
        {
            return new SQLDatePart('WEEK', $dateColumn);
        }

        public static function getYear(string $dateColumn): DBDatePartInterface
        {
            return new SQLDatePart('YEAR', $dateColumn);
        }
    }

}
