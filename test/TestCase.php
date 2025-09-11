<?php

/**
 * Description of TestCase
 * @author goddy
 *
 * Created on: Jul 18, 2025 at 1:33:08 PM
 */

namespace test {

    use test\helpers\TestComment;
    use test\helpers\TestPerformanceScore;
    use test\helpers\TestSeverity;

    final class TestCase implements \JsonSerializable
    {

        private readonly ?string $id;
        private readonly string $description;
        private readonly TestComment $result;
        private readonly TestSeverity $severity;
        private int $repetition = 1;
        private $callback;

        /**
         * Test Execution time in milliseconds
         * @var float
         */
        private float $executionTime = 0;

        /**
         * Overall average test performance in relation to execution time and memory usage
         * @var float
         */
        private float $performanceScore = 0;

        /**
         * Test maximum execution time in milliseconds
         * @var int
         */
        private int $maxExecutionTime = 0;

        /**
         * Create a test case
         * @param TestSeverity $severity Test case severity
         * @param string|null $id Test case unique id
         */
        public function __construct(TestSeverity $severity, ?string $id = null)
        {
            $this->severity = $severity;
            $this->id = strtoupper($id);
        }

        /**
         * Register a test case
         * @param string $description Test description
         * @param callable $callback A callback with the following signature:
         * <code>$callback():bool</code>. True when test passes or false when a test fails
         * @param int $maxExecutionTime Maximum execution time (in milliseconds)
         * a single test case should take. When a test takes longer than this time
         * the performance drops.
         * @param int $repetition A number of times a test should be repeated. By
         * default a test case is run only once
         * @return self
         */
        public function test(string $description, callable $callback, int $maxExecutionTime = 0, int $repetition = 1): self
        {
            $this->maxExecutionTime = $maxExecutionTime * $repetition;
            $this->description = $description;
            $this->repetition = $repetition;
            $this->callback = $callback;
            return $this;
        }

        /**
         * Execute test case and return a test result
         * @return bool True when a test case passes, false otherwise
         */
        public function getResult(): bool
        {
            $cb = $this->callback;
            $passCount = 0;
            for ($i = 0; $i < $this->repetition; $i++) {
                $start = hrtime(true);
                if ($cb()) {
                    ++$passCount;
                }
                $this->executionTime += (hrtime(true) - $start);
            }
            $this->result = $passCount === $this->repetition ? TestComment::PASS : TestComment::FAIL;
            $this->executionTime /= 1E6; //converting into milliseconds
            $this->performanceScore = TestPerformanceScore::calculate($this->maxExecutionTime, $this->executionTime);
            return $this->result === TestComment::PASS;
        }

        /**
         * Get the test performance score in percent
         * @return float Performance score in percent
         */
        public function getPerformanceScore(): float
        {
            return $this->performanceScore;
        }

        /**
         * Get test case execution time
         * @return float Time in milliseconds
         */
        public function getExecutionTime(): float
        {
            return $this->executionTime;
        }

        public function jsonSerialize(): array
        {
            return [
                'id' => $this->id,
                'description' => $this->description,
                'result' => $this->result->name,
                'severity' => $this->severity->name,
                'execution_time_ms' => $this->executionTime,
                'performance' => TestPerformanceScore::check($this->performanceScore)->name,
                'performance_score' => $this->performanceScore,
            ];
        }
    }

}
