<?php

/**
 * Description of TestCase
 * @author coder
 *
 * Created on: May 24, 2024 at 9:47:15 AM
 */

namespace library {

    final class TestCase
    {

        private $value = null;
        private int $pass = 0, $total = 0, $length;
        private ?string $description, $lines = null;
        private $before = null, $after = null;
        private array $results = [];

        public const TYPE_INT = 'integer', TYPE_BOOL = 'boolean', TYPE_DOUBLE = 'double';
        public const TYPE_STRING = 'string', TYPE_ARRAY = 'array', TYPE_OBJECT = 'object';
        public const TYPE_NULL = 'NULL', TYPE_RESOURCE_CLOSED = 'resource (closed)';
        public const TYPE_RESOURCE = 'resource', TYPE_UNKNOWN = 'unknown type';
        private const COLOR_BLACK = 0, COLOR_GREEN = 2, COLOR_RED = 1, COLOR_CYAN = 6;
        private const COLOR_BROWN = 3, COLOR_BLUE = 4, COLOR_PURPLE = 5, COLOR_GREY = 7;

        public function __construct(string $description = null)
        {
            $this->length = strlen($description ?? '');
            $this->description = $description;
        }

        public function __destruct()
        {
            $this->printResults();
        }

        public function done(): array
        {
            $this->results['all'] = $this->total;
            $this->results['passed'] = $this->pass;
            $this->results['failed'] = $this->total - $this->pass;
            return $this->results;
        }

        private function printResults(): void
        {
            if ($this->total > 0) {
                if ($this->description !== null) {
                    $halfLen = ceil(($this->length - strlen($this->description)) / 2);
                    $space = str_repeat(' ', $halfLen);
                    $str = '+' . str_repeat('-', $this->length) . '+' . PHP_EOL;
                    echo $str . '|' . $space . strtoupper($this->description) . $space . '|' . PHP_EOL . $str;
                }
                echo $this->lines . PHP_EOL;
                $diff = $this->total - $this->pass;
                $title = 'Total Tests: ' . $this->total . ' (100%)';
                echo 'Results:' . PHP_EOL . $title . PHP_EOL;
                echo 'Test Passed: ' . $this->pass . ' (' . round($this->pass * 100 / $this->total, 2) . '%)' . PHP_EOL;
                echo 'Test Failed: ' . $diff . ' (' . round($diff * 100 / $this->total, 2) . '%)' . PHP_EOL;
            }
        }

        public function testIf(mixed $value): self
        {
            $this->value = $value;
            return $this;
        }

        private static function toReadable($value)
        {
            if (is_bool($value)) {
                return $value ? 'true' : 'false';
            }
            if ($value === null) {
                return 'NULL';
            }
            if (is_array($value)) {
                return json_encode($value);
            }
            return $value;
        }

        private static function setup(?callable $callback): void
        {
            if ($callback !== null) {
                $callback();
            }
        }

        public function afterEachTest(callable $callback): self
        {
            $this->after = $callback;
        }

        public function beforeEachTest(callable $callback): self
        {
            $this->before = $callback;
        }

        public function matchesWith(string $pattern, string $details = null): self
        {
            return $this->compare($pattern, preg_match($pattern, $this->value) === 1, $details);
        }

        public function notMatchesWith(string $pattern, string $details = null): self
        {
            return $this->compare($pattern, preg_match($pattern, $this->value) === 0, $details);
        }

        public function contains(string $needle, string $details = null): self
        {
            return $this->compare($needle, preg_match('/\b' . $this->value . '\b/', $needle) === 1, $details);
        }

        public function notContains(string $needle, string $details = null): self
        {
            return $this->compare($needle, preg_match('/\b' . $this->value . '\b/', $needle) !== 1, $details);
        }

        public function isNotType(string $type, string $details = null): self
        {
            return $this->compare($type, gettype($this->value) !== $type, $details);
        }

        public function isType(string $type, string $details = null): self
        {
            return $this->compare($type, gettype($this->value) === $type, $details);
        }

        public function hasValues(array $values, string $details = null): self
        {
            return $this->compare($values, \library\Map::hasValues($this->value, $values), $details);
        }

        public function hasKeys($keys, string $details = null): self
        {
            return $this->compare($keys, \library\Map::hasKeys($this->value, $keys), $details);
        }

        public function missingKeys($keys, string $details = null): self
        {
            return $this->compare($keys, !\library\Map::hasKeys($this->value, $keys), $details);
        }

        public function missingValues($values, string $details = null): self
        {
            return $this->compare($values, !\library\Map::hasValues($this->value, $values), $details);
        }

        public function isGreaterThan($value, string $details = null): self
        {
            return $this->compare($value, $this->value > $value, $details);
        }

        public function isLessThan($value, string $details = null): self
        {
            return $this->compare($value, $this->value < $value, $details);
        }

        public function isEmpty(string $details = null): self
        {
            return $this->compare('(empty)', $this->value === '' || $this->value === null, $details);
        }

        public function isNotEmpty(string $details = null): self
        {
            return $this->compare('(not empty)', $this->value == 0 || !empty($this->value), $details);
        }

        public function isFalsy(string $details = null): self
        {
            return $this->compare('falsy', empty($this->value), $details);
        }

        public function isTruthy(string $details = null): self
        {
            return $this->compare('truthy', !empty($this->value), $details);
        }

        public function is($value, string $details = null): self
        {
            return $this->compare($value, $this->value == $value, $details);
        }

        public function isExact($value, string $details = null): self
        {
            return $this->compare($value, $this->value === $value, $details);
        }

        public function isNotExact($value, string $details = null): self
        {
            return $this->compare($value, $this->value !== $value, $details);
        }

        public function isNot($value, string $details = null): self
        {
            return $this->compare($value, $this->value != $value, $details);
        }

        public function isJson(string $details = null): self
        {
            return $this->compare($this->value, is_array(json_decode($this->value, true)), $details);
        }

        public function isXml(string $details = null): self
        {
            return $this->compare($this->value, simplexml_load_string($this->value) !== false, $details);
        }

        public function isYaml(string $details = null): self
        {
            return $this->compare($this->value, yaml_parse($this->value) !== false, $details);
        }

        public function isCsv(string $details = null): self
        {
            return $this->compare($this->value, is_array(str_getcsv($this->value)), $details);
        }

        private function compare($expected, bool $pass, string $details = null): self
        {
            self::setup($this->before);
            $this->total++;
            if ($pass) {
                $this->pass++;
                $str = $this->total . '.Pass ';
                $len = strlen($str);
                $this->lines .= $str;
            } else {
                $str = $this->total . '.Fail ';
                $len = strlen($str);
                $this->lines .= self::setColor($str, self::COLOR_BLACK, self::COLOR_RED);
            }
            $details .= ' Expected: ' . self::toReadable($expected);
            $details .= ', Found: ' . self::toReadable($this->value);
            $count = strlen($details) + $len;
            if ($this->length < $count) {
                $this->length = $count;
            }
            if (!$pass) {
                $details = self::setColor($details, self::COLOR_RED);
            }
            $this->lines .= $details . PHP_EOL;
            self::setup($this->after);
            return $this;
        }

        private static function setColor(?string $text, int $fontColor, ?int $bgColor = null, int $bold = 0): ?string
        {
            if ($text === null) {
                return null;
            }
            $font = 30 + $fontColor;
            $bg = $bgColor !== null ? ';' . (40 + $bgColor) . 'm' : 'm';
            return "\033[$bold;$font{$bg}$text\033[0m";
        }
    }

}
