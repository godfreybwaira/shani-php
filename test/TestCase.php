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
         * @param callable $callback A callback with the following signature:
         * <code>$callback():bool</code>. True when test passes or false when a test fails
         * @param string|null $id Unique test case ID
         * @return self
         * @throws \Exception When Id is not null and exists
         */
        public function test(string $description, callable $callback, ?string $id = null): self
        {
            if ($id !== null) {
                $id = strtoupper($id);
                foreach ($this->testResult as $value) {
                    if ($value['id'] === $id) {
                        throw new \Exception('Test case Id exists: ', $id);
                    }
                }
            }
            $start = hrtime(true);
            $this->testResult[] = [
                'id' => $id,
                'result' => $callback(),
                'duration' => (hrtime(true) - $start) / 1E+9, //converting into seconds
                'description' => $description
            ];
            return $this;
        }
    }

}
