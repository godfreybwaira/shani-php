<?php

/**
 * Description of TestCase
 * @author coder
 *
 * Created on: May 3, 2025 at 2:40:16 PM
 */

namespace test {

    final class TestCase
    {

        private array $testResult;
        public readonly string $description;

        /**
         * Create a test case (scenario) and give it a name
         * @param string $description Test case description (name)
         */
        public function __construct(string $description)
        {
            $this->testResult = [];
            $this->description = $description;
        }

        /**
         * Get collection of individual test result in this case
         * @return array
         */
        public function getResult(): array
        {
            return $this->testResult;
        }

        /**
         * Perform a test case and return a result of a test case
         * @param string $description Test description
         * @param callable $callback A callback that returns boolean. True when
         * test passes or false when a test fails
         * @return self
         */
        public function test(string $description, callable $callback): self
        {
            $this->testResult[] = [
                'result' => $callback(),
                'description' => $description
            ];
            return $this;
        }
    }

}
