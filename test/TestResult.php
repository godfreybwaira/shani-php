<?php

/**
 * Description of TestResult
 * @author goddy
 *
 * Created on: Jul 18, 2025 at 12:44:26 PM
 */

namespace test {

    final class TestResult implements \JsonSerializable
    {

        private const FILENAME_PATTERN = '/^\d{4}(-\d{2}){2}\.\d{4}_test-report\.json/';

        private int $testPassed = 0, $totalTests = 0;
        private readonly TestEnvironment $env;
        private readonly string $description;
        private readonly ?string $location;
        private array $testGroups, $summary;
        private float $executionTime = 0;

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
            $this->summary = [];
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
                $this->testPassed += $group->getTotalTestPassed();
                $this->executionTime += $group->getTotalExecutionTime();
                $this->totalTests += $group->getTotalTests();
            }
            return $this;
        }

        private function setStaging(string $staging): self
        {
            $this->summary['staging'] = $staging;
            return $this;
        }

        #[\Override]
        public function jsonSerialize(): array
        {
            $summary = new TestSummary($this->description, $this->totalTests, $this->testPassed, $this->executionTime);
            $testSummary = $summary->jsonSerialize();
            $testSummary['timestamp'] = date('Y-m-d\TH:i:s');

            return [
                'summary' => array_merge($this->summary, $testSummary),
                'environment' => $this->env,
                'groups' => $this->testGroups
            ];
        }

        public static function processResult(TestResult $result, string $staging): bool
        {
            $result->setStaging($staging);
            $report = json_encode($result->jsonSerialize(), JSON_PRETTY_PRINT);
            $result->save($report);
            return $result->totalTests === $result->testPassed;
        }

        private function save(string $data): self
        {
            if ($this->location !== null) {
                $filename = date('Y-m-d.Hi') . '_test-report.json';
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
