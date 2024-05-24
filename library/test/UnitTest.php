<?php

/**
 * Description of UnitTest
 * @author coder
 *
 * Created on: May 24, 2024 at 9:47:15 AM
 */

namespace library\test {

    final class UnitTest
    {

        private $value = null;
        private int $pass = 0, $total = 0, $length;
        private ?string $description, $lines = null;

        public const TYPE_INT = 'integer', TYPE_BOOL = 'boolean', TYPE_DOUBLE = 'double';
        public const TYPE_STRING = 'string', TYPE_ARRAY = 'array', TYPE_OBJECT = 'object';
        public const TYPE_NULL = 'NULL', TYPE_RESOURCE_CLOSED = 'resource (closed)';
        public const TYPE_RESOURCE = 'resource', TYPE_UNKNOWN = 'unknown type';
        private const COLOR_BLACK = 0, COLOR_GREEN = 2, COLOR_RED = 1, COLOR_CYAN = 6;
        private const COLOR_BROWN = 3, COLOR_BLUE = 4, COLOR_PURPLE = 5, COLOR_GREY = 7;

        public function __construct(string $description = null)
        {
            $this->length = strlen($description);
            $this->description = $description;
        }

        public function __destruct()
        {
            if ($this->total > 1) {
                if ($this->description !== null) {
                    $halfLen = ceil(($this->length - strlen($this->description)) / 2);
                    $space = str_repeat(' ', $halfLen);
                    $str = '+' . str_repeat('-', $this->length) . '+' . PHP_EOL;
                    echo $str . '|' . $space . strtoupper($this->description) . $space . '|' . PHP_EOL . $str;
                }
                echo $this->lines . PHP_EOL;
                $title = 'Total Tests: ' . $this->total . ' (100%)';
                echo 'Results:' . PHP_EOL;
                echo $title . PHP_EOL;
                echo 'Test Passed: ' . $this->pass . ' (' . round($this->pass * 100 / $this->total, 2) . '%)' . PHP_EOL;
                $diff = $this->total - $this->pass;
                echo 'Test Failed: ' . $diff . ' (' . round($diff * 100 / $this->total, 2) . '%)' . PHP_EOL;
            }
        }

        public function testIf(mixed $value): self
        {
            $this->value = $value;
            return $this;
        }

        public function isNotType(string $type, string $details = null): self
        {
            return $this->compare(gettype($this->value) !== $type, $details);
        }

        public function isType(string $type, string $details = null): self
        {
            return $this->compare(gettype($this->value) === $type, $details);
        }

        public function has($value, string $details = null): self
        {
            return $this->compare(is_array($this->value) && in_array($value, $this->value), $details);
        }

        public function missing($value, string $details = null): self
        {
            return $this->compare(is_array($this->value) && !in_array($value, $this->value), $details);
        }

        public function isGreaterThan($value, string $details = null): self
        {
            return $this->compare($this->value > $value, $details);
        }

        public function isLessThan($value, string $details = null): self
        {
            return $this->compare($this->value < $value, $details);
        }

        public function isEmpty(string $details = null): self
        {
            return $this->compare($this->value === '' || $this->value === null, $details);
        }

        public function isNotEmpty(string $details = null): self
        {
            return $this->compare($this->value == 0 || !empty($this->value), $details);
        }

        public function isFalsy(string $details = null): self
        {
            return $this->compare(empty($this->value), $details);
        }

        public function isTruthy(string $details = null): self
        {
            return $this->compare(!empty($this->value), $details);
        }

        public function is($value, string $details = null): self
        {
            return $this->compare($this->value == $value, $details);
        }

        public function isExact($value, string $details = null): self
        {
            return $this->compare($this->value === $value, $details);
        }

        public function isNotExact($value, string $details = null): self
        {
            return $this->compare($this->value !== $value, $details);
        }

        public function isNot($value, string $details = null): self
        {
            return $this->compare($this->value != $value, $details);
        }

        private function compare(bool $pass, string $details = null): self
        {
            $this->total++;
            if ($pass) {
                $this->pass++;
                $str = ' ' . $this->total . '.Pass ';
                $len = strlen($str);
                $this->lines .= $str;
            } else {
                $str = ' ' . $this->total . '.Fail ';
                $len = strlen($str);
                $this->lines .= self::setColor($str, self::COLOR_BLACK, self::COLOR_RED);
            }
            if ($details !== null) {
                $count = strlen($details) + $len + 1;
                if ($this->length < $count) {
                    $this->length = $count;
                }
                if (!$pass) {
                    $details = self::setColor($details, self::COLOR_RED);
                }
                $this->lines .= ' ' . $details . PHP_EOL;
            }
            return $this;
        }

        private static function setColor(string $text, int $fontColor, ?int $bgColor = null, int $bold = 0): string
        {
            $font = 30 + $fontColor;
            $bg = $bgColor !== null ? ';' . (40 + $bgColor) . 'm' : 'm';
            return "\033[$bold;$font{$bg}$text\033[0m";
        }
    }

}
