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
        private ?string $details = null;

        public function __constructor(string $description = null)
        {
            if ($description !== null) {
                $count = strlen($description);
                $str = str_repeat('-', $count + 3) . PHP_EOL;
                echo $str . strtoupper($description) . PHP_EOL . $str;
            }
        }

        public function checkIf(mixed $value, string $details = null): self
        {
            $this->details = $details;
            $this->value = $value;
        }

        public function has($value): self
        {
            return $this->compare(is_array($this->value) && in_array($value, $this->value));
        }

        public function missing($value): self
        {
            return $this->compare(is_array($this->value) && !in_array($value, $this->value));
        }

        public function isGreaterThan($value): self
        {
            return $this->compare($this->value > $value);
        }

        public function isLessThan($value): self
        {
            return $this->compare($this->value < $value);
        }

        public function isEmpty(): self
        {
            return $this->compare($this->value === '' || $this->value === null);
        }

        public function isNotEmpty(): self
        {
            return $this->compare($this->value !== '' || $this->value !== null);
        }

        public function isFalsy(): self
        {
            return $this->compare(empty($this->value));
        }

        public function isTruthy(): self
        {
            return $this->compare(!empty($this->value));
        }

        public function is($value): self
        {
            return $this->compare($this->value == $value);
        }

        public function isExact($value): self
        {
            return $this->compare($this->value === $value);
        }

        public function isNotExact($value): self
        {
            return $this->compare($this->value !== $value);
        }

        public function isNot($value): self
        {
            return $this->compare($this->value != $value);
        }

        private function compare(bool $value): self
        {
            if ($value) {
                echo $this->details . PHP_EOL;
                echo 'Pass' . PHP_EOL;
            } else {
                echo 'Fail' . PHP_EOL;
            }
            return $this;
        }
    }

    $test = new TestCase();
    $test->checkIf(2 + 3, 'Check if 2 + 3 gives 5')->is(5);
}
