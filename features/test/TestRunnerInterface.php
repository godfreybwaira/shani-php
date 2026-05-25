<?php

namespace features\test {

    /**
     * Contract for running tests within the framework.
     * Defines lifecycle hooks before and after test execution,
     * and the main test runner method.
     *
     * @author goddy
     * @created May 15, 2026 at 3:48:15 PM
     */
    interface TestRunnerInterface
    {

        /**
         * Execute the test and return its result.
         *
         * @return TestResult Result object containing test outcome details.
         */
        public function runTest(): TestResult;

        /**
         * Hook executed before the test runs.
         * Useful for setup operations such as initializing resources.
         *
         * @return void
         */
        public function beforeTest(): void;

        /**
         * Hook executed after the test completes.
         * Can be used for cleanup or logging based on the result.
         *
         * @param TestResult $result The result of the executed test.
         * @return void
         */
        public function afterTest(TestResult $result): void;
    }

}
