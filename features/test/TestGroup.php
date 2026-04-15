<?php

/**
 * Description of TestGroup
 * @author goddy
 *
 * Created on: Jul 18, 2025 at 1:14:19 PM
 */

namespace features\test {

    use features\test\helpers\TestSummary;

    final class TestGroup implements \JsonSerializable
    {

        private float $executionTime = 0, $performanceScore = 0;
        private int $testPassed = 0, $totalTests = 0;
        private readonly string $description;
        private array $testCases;

        /**
         * Create a test case group so that all cases with similar nature or behavior
         * can be grouped together
         * @param string $description Group description or name
         */
        public function __construct(string $description)
        {
            $this->description = $description;
            $this->testCases = [];
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
                ++$this->totalTests;
            }
            return $this;
        }

        /**
         * Execute all test cases and return a general result
         * @return bool True if all tests pass, false otherwise
         */
        public function getResult(): bool
        {
            foreach ($this->testCases as $case) {
                if ($case->getResult()) {
                    ++$this->testPassed;
                }
                $this->performanceScore += $case->getPerformanceScore();
                $this->executionTime += $case->getExecutionTime();
            }
            return $this->testPassed === $this->totalTests;
        }

        /**
         * Get total test performance score (in percent) in this group
         * @return float Performance score in percent
         */
        public function getTotalPerformanceScore(): float
        {
            return $this->performanceScore;
        }

        /**
         * Get total execution time (in milliseconds) in this group
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
                'summary' => new TestSummary(
                        $this->description, $this->totalTests, $this->testPassed,
                        $this->executionTime, $this->performanceScore
                ),
                'cases' => $this->testCases
            ];
        }
    }

}
