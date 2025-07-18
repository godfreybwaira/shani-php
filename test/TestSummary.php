<?php

/**
 * Description of TestSummary
 * @author goddy
 *
 * Created on: Jul 18, 2025 at 12:48:22 PM
 */

namespace test {

    final class TestSummary implements \JsonSerializable
    {

        public readonly int $totalTests, $testPassed;
        public readonly float $executionTime;
        public readonly string $description;

        /**
         * Create a summary of what happened on the test you have done. It can be on each module or entire test scenario
         * @param string $description Description of the test
         * @param int $totalTests Total tests done
         * @param int $testPassed Total test passed
         * @param float $executionTime Total test execution time
         */
        public function __construct(string $description, int $totalTests, int $testPassed, float $executionTime)
        {
            $this->description = $description;
            $this->totalTests = $totalTests;
            $this->testPassed = $testPassed;
            $this->executionTime = $executionTime;
        }

        #[\Override]
        public function jsonSerialize(): array
        {
            return[
                'description' => $this->description,
                'total_tests' => $this->totalTests,
                'test_passed' => $this->testPassed,
                'test_failed' => ($this->totalTests - $this->testPassed),
                'success_rate' => round(($this->testPassed / $this->totalTests) * 100, 2),
                'execution_time' => $this->executionTime,
                'comments' => ($this->totalTests === $this->testPassed) ? TestComment::PASS->name : TestComment::FAIL->name
            ];
        }
    }

}
