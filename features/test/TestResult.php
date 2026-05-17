<?php

/**
 * Description of TestResult
 * @author goddy
 *
 * Created on: Jul 18, 2025 at 12:44:26 PM
 */

namespace features\test {

    use features\storage\LocalStorage;
    use features\test\helpers\TestEnvironment;
    use features\test\helpers\TestSummary;
    use shani\launcher\Framework;

    final class TestResult implements \JsonSerializable
    {

        /** Location on disk where test report will be saved */
        public const REPORTS_STORAGE = Framework::DIR_SERVER_STORAGE . DIRECTORY_SEPARATOR . 'tests';

        private float $executionTime = 0, $performanceScore = 0;
        private int $testPassed = 0, $totalTests = 0;
        private readonly TestEnvironment $env;
        private readonly string $description;
        private array $testGroups;

        /**
         * Create general test result for your application
         * @param string $description Description about this Test or result name
         */
        public function __construct(string $description)
        {
            $this->env = new TestEnvironment();
            $this->description = $description;
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

        /**
         * Get the total number of tests done
         * @return int
         */
        public function getTotalTests(): int
        {
            return $this->totalTests;
        }

        private function save(string $data): self
        {
            $filename = date('Y-m-d.Hi') . '_test_report.json';
            if (!is_dir(self::REPORTS_STORAGE)) {
                mkdir(self::REPORTS_STORAGE, LocalStorage::FILE_MODE, true);
            }
            file_put_contents(self::REPORTS_STORAGE . DIRECTORY_SEPARATOR . $filename, $data);
            return $this;
        }
    }

}
