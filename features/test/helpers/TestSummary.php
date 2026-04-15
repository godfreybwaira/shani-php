<?php

/**
 * Description of TestSummary
 * @author goddy
 *
 * Created on: Jul 18, 2025 at 12:48:22 PM
 */

namespace features\test\helpers {

    final class TestSummary implements \JsonSerializable
    {

        private readonly float $executionTime, $performanceScore;
        private readonly int $totalTests, $testPassed;
        private readonly string $description;

        /**
         * Create a summary of what happened on the test you have done. It can be on each module or entire test scenario
         * @param string $description Description of the test
         * @param int $totalTests Total tests done
         * @param int $testPassed Total test passed
         * @param float $executionTime Total test execution time in milliseconds
         * @param float $performanceScore Performance score (in percent)
         */
        public function __construct(
                string $description, int $totalTests, int $testPassed,
                float $executionTime, float $performanceScore
        )
        {
            $this->performanceScore = $performanceScore;
            $this->executionTime = $executionTime;
            $this->description = $description;
            $this->totalTests = $totalTests;
            $this->testPassed = $testPassed;
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
                'execution_time_ms' => $this->executionTime,
                'performance' => TestPerformanceScore::check($this->performanceScore)->name,
                'performance_score' => $this->performanceScore,
                'comments' => ($this->totalTests === $this->testPassed) ? TestComment::PASS->name : TestComment::FAIL->name
            ];
        }
    }

}
