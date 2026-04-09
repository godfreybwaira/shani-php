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

        private float $executionTime = 0, $performanceScore = 0;
        private int $testPassed = 0, $totalTests = 0;
        private readonly TestEnvironment $env;
        private readonly string $description;
        private readonly ?string $location;
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
            return [
                'metadata' => [
                    'version' => '1.0',
                    'report_datetime' => date('Y-m-d\TH:i:s')
                ],
                'summary' => new TestSummary(
                        $this->description, $this->totalTests, $this->testPassed,
                        $this->executionTime, $this->performanceScore
                ),
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
                $this->performanceScore += $group->getTotalPerformanceScore();
                $this->totalTests += $group->getTotalTests();
            }
            $report = json_encode($this->jsonSerialize(), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $this->save($report);
            return $this->totalTests === $this->testPassed;
        }

        private function save(string $data): self
        {
            if ($this->location !== null) {
                $filename = date('Y-m-d.Hi') . '_test_report.json';
                file_put_contents($this->location . '/' . $filename, $data);
            } else {
                echo $data;
            }
            return $this;
        }
    }

}
