<?php

/**
 * Description of TestResult
 * @author goddy
 *
 * Created on: Jul 18, 2025 at 12:44:26 PM
 */

namespace test {

    use test\helpers\TestEnvironment;
    use test\helpers\TestSummary;

    final class TestResult implements \JsonSerializable
    {

        private const FILENAME_PATTERN = '/^\d{4}(-\d{2}){2}\.\d{3}_test-report\.json/';

        private int $testPassed = 0, $totalTests = 0;
        private readonly TestEnvironment $env;
        private readonly string $description;
        private readonly ?string $location;
        private float $executionTime = 0;
        private array $testGroups;

        /**
         * Create general test result for your application
         * @param string $description Description about this Test or result name
         * @param string|null $location Location on disk where test report will be saved
         */
        public function __construct(string $description, string $location = null)
        {
            $this->env = new TestEnvironment();
            $this->description = $description;
            $this->location = $location;
            $this->testGroups = [];
        }

        /**
         * Add Test group in a test result
         * @param TestGroup $groups Test group
         * @return self
         */
        public function addGroup(TestGroup ...$groups): self
        {
            foreach ($groups as $group) {
                $this->testGroups[] = $group;
            }
            return $this;
        }

        #[\Override]
        public function jsonSerialize(): array
        {
            $summary = new TestSummary($this->description, $this->totalTests, $this->testPassed, $this->executionTime);
            return [
                'metadata' => [
                    'version' => '1.0',
                    'timestamp' => date('Y-m-d\TH:i:s')
                ],
                'summary' => $summary,
                'environment' => $this->env,
                'groups' => $this->testGroups
            ];
        }

        /**
         * Execute all test groups and return a general test result
         * @return bool True if a test passes, false otherwise
         */
        public function getResult(): bool
        {
            foreach ($this->testGroups as $group) {
                $group->getResult();
                $this->testPassed += $group->getTotalTestPassed();
                $this->executionTime += $group->getTotalExecutionTime();
                $this->totalTests += $group->getTotalTests();
            }
            $report = json_encode($this->jsonSerialize(), JSON_PRETTY_PRINT);
            $this->save($report);
            return $this->totalTests === $this->testPassed;
        }

        private function save(string $data): self
        {
            if ($this->location !== null) {
                $date = date('Y-m-d.Hi');
                $filename = substr($date, 0, strlen($date) - 1) . '_test-report.json';
                if (preg_match(self::FILENAME_PATTERN, $filename) !== 1) {
                    throw new \Exception('Invalid file name');
                }
                file_put_contents($this->location . '/' . $filename, $data);
            } else {
                echo $data;
            }
            return $this;
        }
    }

}
