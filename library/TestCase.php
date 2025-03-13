<?php

/**
 * A unit test API
 * @author coder
 *
 * Created on: May 24, 2024 at 9:47:15 AM
 */

namespace library {

    final class TestCase
    {

        private array $cases;
        private $testedValue = null;
        private ?string $description;
        private int $totalPasses = 0, $totalCases = 0;

        public const TYPE_INT = 'integer', TYPE_BOOL = 'boolean', TYPE_DOUBLE = 'double';
        public const TYPE_STRING = 'string', TYPE_ARRAY = 'array', TYPE_OBJECT = 'object';
        public const TYPE_NULL = 'NULL', TYPE_RESOURCE_CLOSED = 'resource (closed)';
        public const TYPE_RESOURCE = 'resource', TYPE_UNKNOWN = 'unknown type';

        public function __construct(?string $description = null)
        {
            $this->description = $description;
            $this->cases = [];
        }

        public function done(): array
        {
            return [
                'test' => [
                    'description' => $this->description, 'date' => gmdate(DATE_RFC2822),
                    'cases' => $this->totalCases, 'passes' => $this->totalPasses,
                    'fails' => ($this->totalCases - $this->totalPasses)
                ],
                'cases' => $this->cases
            ];
        }

        /**
         * Create and start a unit test.
         * @param mixed $expectedValue A target value to test
         * @return self
         */
        public function testIf(mixed $expectedValue): self
        {
            $this->testedValue = $expectedValue;
            return $this;
        }

        private static function toReadable($expectedValue)
        {
            if (is_bool($expectedValue)) {
                return $expectedValue ? 'true' : 'false';
            }
            if ($expectedValue === null) {
                return 'NULL';
            }
            if (is_array($expectedValue)) {
                return json_encode($expectedValue);
            }
            return $expectedValue;
        }

        public function matchesWith(string $pattern, string $details = null): self
        {
            $details ??= 'Test if "' . $this->testedValue . '" matches with a pattern ' . $pattern;
            $pass = preg_match($pattern, $this->testedValue) === 1;
            return $this->getResult($pass, $details, true, $pass);
        }

        public function notMatchesWith(string $pattern, string $details = null): self
        {
            $details ??= 'Test if "' . $this->testedValue . '" not matches with a pattern ' . $pattern;
            $pass = preg_match($pattern, $this->testedValue) === 0;
            return $this->getResult($pass, $details, true, $pass);
        }

        public function containsWord(string $word, string $details = null): self
        {
            $details ??= 'Test if "' . $this->testedValue . '" contains a word "' . $word . '"';
            $pass = preg_match('/\b' . preg_quote($word, '/') . '\b/i', $this->testedValue) === 1;
            return $this->getResult($pass, $details, true, $pass);
        }

        public function notContainsWord(string $word, string $details = null): self
        {
            $details ??= 'Test if "' . $this->testedValue . '" not contains a word "' . $word . '"';
            $pass = preg_match('/\b' . preg_quote($word, '/') . '\b/i', $this->testedValue) !== 1;
            return $this->getResult($pass, $details, true, $pass);
        }

        public function isNotType(string $type, string $details = null): self
        {
            $details ??= 'Test if ' . self::toReadable($this->testedValue) . ' is not of type ' . $type;
            $actualType = gettype($this->testedValue);
            return $this->getResult($actualType !== $type, $details, $type, $actualType);
        }

        public function isType(string $type, string $details = null): self
        {
            $details ??= 'Test if ' . self::toReadable($this->testedValue) . ' is of type ' . $type;
            $actualType = gettype($this->testedValue);
            return $this->getResult($actualType === $type, $details, $type, $actualType);
        }

        public function hasValues(string|array $expectedValues, string $details = null): self
        {
            $details ??= 'Test if a given array contains all value(s): ' . self::toReadable($expectedValues);
            $pass = \library\Map::hasValues($this->testedValue, $expectedValues);
            return $this->getResult($pass, $details, true, $pass);
        }

        public function hasKeys(string|array $keys, string $details = null): self
        {
            $details ??= 'Test if a given array contains all key(s): ' . self::toReadable($keys);
            $pass = \library\Map::hasKeys($this->testedValue, $keys);
            return $this->getResult($pass, $details, true, $pass);
        }

        public function missingKeys(string|array $keys, string $details = null): self
        {
            $details ??= 'Test if a given array missing any of the key(s): ' . self::toReadable($keys);
            $pass = !\library\Map::hasKeys($this->testedValue, $keys);
            return $this->getResult($pass, $details, true, $pass);
        }

        public function missingValues(string|array $expectedValues, string $details = null): self
        {
            $details ??= 'Test if a given array missing any of the value(s): ' . self::toReadable($expectedValues);
            $pass = !\library\Map::hasValues($this->testedValue, $expectedValues);
            return $this->getResult($pass, $details, true, $pass);
        }

        /**
         * Test if a given tested value satisfies a given equation solved by a callback
         * @param callable $callback A callback that accepts a tested value as argument
         * and returns true if test passes, false otherwise.
         * @param string $details Test description
         * @return self
         */
        public function satisfies(callable $callback, string $details): self
        {
            $pass = $callback($this->testedValue);
            return $this->getResult($pass, $details, true, $pass);
        }

        public function isGreaterThan(float $expectedValue, string $details = null): self
        {
            $details ??= 'Test if ' . $this->testedValue . ' is greater than ' . $expectedValue;
            return $this->getResult($this->testedValue > $expectedValue, $details, $expectedValue, $this->testedValue);
        }

        public function isLessThan(float $expectedValue, string $details = null): self
        {
            $details ??= 'Test if ' . $this->testedValue . ' is less than ' . $expectedValue;
            return $this->getResult($this->testedValue < $expectedValue, $details, $expectedValue, $this->testedValue);
        }

        public function isEmpty(string $details = null): self
        {
            $details ??= 'Test if "' . $this->testedValue . '" is NULL or empty string';
            return $this->getResult($this->testedValue === '' || $this->testedValue === null, $details, $this->testedValue, $this->testedValue);
        }

        public function isNotEmpty(string $details = null): self
        {
            $details ??= 'Test if "' . $this->testedValue . '" is not NULL or empty string';
            return $this->getResult($this->testedValue !== '' || $this->testedValue !== null, $details, $this->testedValue, $this->testedValue);
        }

        public function isFalsy(string $details = null): self
        {
            $details ??= 'Test if "' . $this->testedValue . '" is NULL, zero, false, or empty string';
            return $this->getResult(empty($this->testedValue), $details, false, $this->testedValue);
        }

        public function isTruthy(string $details = null): self
        {
            $details ??= 'Test if "' . $this->testedValue . '"  is not NULL, zero, false, or empty string';
            return $this->getResult(!empty($this->testedValue), $details, true, $this->testedValue);
        }

        public function is($expectedValue, string $details = null): self
        {
            $details ??= 'Test if ' . $this->testedValue . ' equals ' . self::toReadable($expectedValue);
            return $this->getResult($this->testedValue == $expectedValue, $details, $expectedValue, $this->testedValue);
        }

        public function isExact($expectedValue, string $details = null): self
        {
            $details = 'Test if ' . $this->testedValue . ' is exact ' . self::toReadable($expectedValue);
            return $this->getResult($this->testedValue === $expectedValue, $details, $expectedValue, $this->testedValue);
        }

        public function isNotExact($expectedValue, string $details = null): self
        {
            $details ??= 'Test if ' . $this->testedValue . ' is not exact ' . self::toReadable($expectedValue);
            return $this->getResult($this->testedValue !== $expectedValue, $details, $expectedValue, $this->testedValue);
        }

        public function isNot($expectedValue, string $details = null): self
        {
            $details ??= 'Test if ' . $this->testedValue . ' is not equals ' . self::toReadable($expectedValue);
            return $this->getResult($this->testedValue != $expectedValue, $details, $expectedValue, $this->testedValue);
        }

        public function isJson(string $details = null): self
        {
            $details ??= 'Test if given value is can be a valid JSON';
            $pass = is_array(json_decode($this->testedValue, true));
            return $this->getResult($pass, $details, true, $pass);
        }

        public function isXml(string $details = null): self
        {
            $details ??= 'Test if given value is can be a valid XML';
            $pass = simplexml_load_string($this->testedValue) !== false;
            return $this->getResult($pass, $details, true, $pass);
        }

        public function isYaml(string $details = null): self
        {
            $details ??= 'Test if given value is can be a valid YAML';
            $pass = yaml_parse($this->testedValue) !== false;
            return $this->getResult($pass, $details, true, $pass);
        }

        public function isCsv(string $details = null): self
        {
            $details ??= 'Test if given string is can be a valid CSV';
            $pass = is_array(str_getcsv($this->testedValue));
            return $this->getResult($pass, $details, true, $pass);
        }

        private function getResult(bool $pass, string $details, $expectedValue, $foundValue): self
        {
            $this->totalCases++;
            if ($pass) {
                $this->totalPasses++;
            }
            $this->cases[] = [
                'description' => $details,
                'expected' => $expectedValue,
                'found' => $foundValue,
                'result' => $pass ? 'PASS' : 'FAIL'
            ];
            return $this;
        }
    }

}
