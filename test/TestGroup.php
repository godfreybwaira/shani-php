<?php

/**
 * Description of TestGroup
 * @author goddy
 *
 * Created on: Jul 18, 2025 at 1:14:19 PM
 */

namespace test {

    final class TestGroup implements \JsonSerializable
    {

        private int $testPassed = 0, $totalTests = 0;
        private readonly string $description;
        private float $executionTime = 0;
        private array $testCases;

        /**
         * Create a test case group so that all cases with similar nature or behavior
         * can be grouped together
         * @param string $description Group description or name
         */
        public function __construct(string $description)
        {
            $this->description = $description;
            $this->cases = [];
        }

        /**
         * Add a test case to a test group
         * @param TestCase $cases Test case object
         * @return self
         */
        public function addCase(TestCase ...$cases): self
        {
            foreach ($cases as $case) {
                $this->testCases[] = $case;
                $this->executionTime += $case->getExecutionTime();
                if ($case->getResult() === TestComment::PASS) {
                    ++$this->testPassed;
                }
                ++$this->totalTests;
            }
            return $this;
        }

        /**
         * Get test group summary
         * @return TestSummary Test group summary
         */
        public function getSummary(): TestSummary
        {
            return new TestSummary($this->description, $this->totalTests, $this->testPassed, $this->executionTime);
        }

        /**
         * Get total execution time in this group
         * @return float
         */
        public function getTotalExecutionTime(): float
        {
            return $this->executionTime;
        }

        /**
         * Get total tests passed in this group
         * @return int Number of tests passed in a group
         */
        public function getTotalTestPassed(): int
        {
            return $this->testPassed;
        }

        /**
         * Get total tests in this group
         * @return int Number of tests in a group
         */
        public function getTotalTests(): int
        {
            return $this->totalTests;
        }

        #[\Override]
        public function jsonSerialize(): array
        {
            return [
                'summary' => $this->getSummary(),
                'cases' => $this->testCases
            ];
        }
    }

}
