<?php

/**
 * Description of TestCase
 * @author goddy
 *
 * Created on: Jul 18, 2025 at 1:33:08 PM
 */

namespace test {

    final class TestCase implements \JsonSerializable
    {

        private readonly ?string $id;
        private readonly string $description;
        private readonly TestComment $result;
        private readonly TestSeverity $severity;

        /**
         * Test Execution time in seconds
         * @var float
         */
        private float $executionTime = 0;

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
         * Perform a test case and return a result of a test case
         * @param string $description Test description
         * @param callable $callback A callback with the following signature:
         * <code>$callback():bool</code>. True when test passes or false when a test fails
         * @return self
         */
        public function test(string $description, callable $callback): self
        {
            $this->description = $description;
            $start = hrtime(true);
            $this->result = $callback() ? TestComment::PASS : TestComment::FAIL;
            $this->executionTime = (hrtime(true) - $start) / 1E9; //converting into seconds
            return $this;
        }

        /**
         * Get test case result.
         * @return TestComment Test result
         */
        public function getResult(): TestComment
        {
            return $this->result;
        }

        /**
         * Get test case execution time
         * @return float Time in seconds
         */
        public function getExecutionTime(): float
        {
            return $this->executionTime;
        }

        public function jsonSerialize(): array
        {
            return [
                'id' => $this->id,
                'result' => $this->result->name,
                'severity' => $this->severity->name,
                'execution_time' => $this->executionTime,
                'description' => $this->description
            ];
        }
    }

}
