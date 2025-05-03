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

        private array $testCases = [];

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
            $content = null;
            $cases = $result->getCases();
            $separator = str_repeat('-', 10);
            $pass = $fail = $longestString = 0;
            $passLabel = ConsolePrinter::colorText(' PASS ', ConsolePrinter::COLOR_WHITE, ConsolePrinter::COLOR_GREEN);
            $failLabel = ConsolePrinter::colorText(' FAIL ', ConsolePrinter::COLOR_WHITE, ConsolePrinter::COLOR_RED);
            foreach ($cases as $case) {
                $longestString = max(mb_strlen($case->description), $longestString);
                $content .= $case->description . PHP_EOL;
                $caseResults = $case->getResult();
                foreach ($caseResults as $cr) {
                    if ($cr['result']) {
                        ++$pass;
                    } else {
                        ++$fail;
                    }
                    $longestString = max(mb_strlen($cr['description']), $longestString);
                    $content .= $cr['description'] . $separator . ($cr['result'] ? $passLabel : $failLabel) . PHP_EOL;
                }
            }
            $total = $pass + $fail;
            $percentPass = round($pass * 100 / $total, 2);
            $content .= str_repeat('--', 50) . PHP_EOL;
            $content .= 'TEST RESULTS:' . PHP_EOL;
            $content .= 'TOTAL TESTS: ' . $total . PHP_EOL;
            $content .= 'TEST PASSED: ' . $pass . ' (' . $percentPass . '%)' . PHP_EOL;
            $content .= 'TEST FAILED: ' . $fail . ' (' . (100 - $percentPass) . '%)' . PHP_EOL;
            $content .= str_repeat('--', 50) . PHP_EOL;
            print_r($content);
            return $pass === $total;
        }
    }

}
