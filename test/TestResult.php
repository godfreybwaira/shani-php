<?php

/**
 * Description of TestResult
 * @author coder
 *
 * Created on: May 3, 2025 at 2:55:08 PM
 */

namespace test {

    use shani\core\log\ConsolePrinter;

    final class TestResult
    {

        public const FILENAME_PATTERN = '/^\d{4}(-\d{2}){2}\.\d{4}_test-report\.txt$/';
        public const KEYWORD_PASS = ' TEST PASSED';
        public const KEYWORD_FAIL = ' TEST FAILED';
        public const KEYWORD_TIMESTAMP = ' TIMESTAMP';
        public const KEYWORD_CASES = ' TOTAL TESTS';
        public const KEYWORD_COMMENTS = ' COMMENTS';

        private array $testCases = [];
        private ?string $description;
        private ?string $location = null;

        /**
         * Create a test result for your application. Test result acts like an
         * envelope for your test cases
         * @param string $description Result description
         * @param string $location Location where test report will be saved.
         */
        public function __construct(string $description = null, string $location = null)
        {
            $this->description = $description;
            if ($location !== null) {
                $this->saveTo($location);
            }
        }

        /**
         * Location where test report will be saved.
         * @param string $location Location on disk
         * @return self
         */
        public function saveTo(string $location): self
        {
            $this->location = $location;
            return $this;
        }

        /**
         * Collect all test case results together
         * @param TestCase $cases Test case object
         * @return self
         */
        public function addCase(TestCase ...$cases): self
        {
            foreach ($cases as $case) {
                $this->testCases[] = $case;
            }
            return $this;
        }

        /**
         * Returns array of TestCase object
         * @return self
         */
        public function getCases(): array
        {
            return $this->testCases;
        }

        /**
         * Process test results
         * @param TestResult $result TestResult object
         * @return bool Returns true if all test passes, false otherwise;
         */
        public static function processResult(TestResult $result): bool
        {
            $content = $values = [];
            $cases = $result->getCases();
            $pass = $fail = $longestString = $count = 0;
            $passLabel = '[ ' . $result->formatLabel('PASS', ConsolePrinter::COLOR_GREEN) . ' ]';
            $failLabel = '[ ' . $result->formatLabel('FAIL', ConsolePrinter::COLOR_RED) . ' ]';
            foreach ($cases as $case) {
                $caseResults = $case->getResult();
                $label = strtoupper($case->description) . ' (' . count($caseResults) . ' Tests)';
                $content[] = PHP_EOL . $result->formatLabel($label, ConsolePrinter::COLOR_YELLOW);
                $values[] = null;
                foreach ($caseResults as $cr) {
                    if ($cr['result']) {
                        ++$pass;
                    } else {
                        ++$fail;
                    }
                    $details = ' ' . (++$count) . '. ' . $cr['description'];
                    $longestString = max(strlen($details), $longestString);
                    $content[] = $details;
                    $values[] = $cr['result'] ? $passLabel : $failLabel;
                }
            }
            $longestString += 10;
            $totalTests = $pass + $fail;
            $percentPass = round($pass * 100 / $totalTests, 2);
            $content[] = PHP_EOL . str_repeat('-', $longestString + 1);
            $values[] = null;
            $content[] = self::KEYWORD_CASES;
            $values[] = $totalTests;
            $content[] = self::KEYWORD_PASS;
            $values[] = $pass . ' (' . $percentPass . '%)';
            $content[] = self::KEYWORD_FAIL;
            $values[] = $fail . ' (' . (100 - $percentPass) . '%)';
            $content[] = self::KEYWORD_COMMENTS;
            $values[] = $pass === $totalTests ? $passLabel : $failLabel;
            $content[] = self::KEYWORD_TIMESTAMP;
            $values[] = time();

            $str = $result->description !== null ? strtoupper($result->description) . PHP_EOL : null;
            $str .= self::formatContent($content, $values, $longestString);
            $result->save($str);
            return $pass === $totalTests;
        }

        private static function formatContent(array &$content, array &$values, int $length): string
        {
            $size = count($values);
            $str = null;
            for ($i = 0; $i < $size; $i++) {
                if ($values[$i] !== null) {
                    $mylen = strlen($content[$i]);
                    $str .= $content[$i] . ' ' . str_repeat('-', $length - $mylen) . ' ' . $values[$i] . PHP_EOL;
                } else {
                    $str .= $content[$i] . PHP_EOL;
                }
            }
            return $str;
        }

        private function save(string $data): self
        {
            if ($this->location !== null) {
                $filename = date('Y-m-d.Hi') . '_test-report.txt';
                if (preg_match(self::FILENAME_PATTERN, $filename) !== 1) {
                    throw new \Exception('Invalid file name');
                }
                file_put_contents($this->location . '/' . $filename, $data);
            } else {
                echo $data;
            }
            return $this;
        }

        private function formatLabel(string $label, ConsolePrinter $textColor): string
        {
            if ($this->location !== null) {
                return $label;
            }
            return ConsolePrinter::colorText($label, $textColor);
        }
    }

}
