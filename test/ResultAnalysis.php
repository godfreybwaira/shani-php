<?php

/**
 * Test result analysis
 * @author coder
 *
 * Created on: May 5, 2025 at 10:32:52 AM
 */

namespace test {

    use lib\ds\map\ReadableMap;

    final class ResultAnalysis implements \JsonSerializable
    {

        private readonly int $timestamp, $cases;
        private readonly float $passed, $failed;

        private function __construct(int $timestamp, int $cases, float $passed, float $failed)
        {
            $this->timestamp = $timestamp;
            $this->passed = $passed;
            $this->failed = $failed;
            $this->cases = $cases;
        }

        public function jsonSerialize(): array
        {
            return[
                'timestamp' => $this->timestamp,
                'cases' => $this->cases,
                'passed' => $this->passed,
                'failed' => $this->failed,
            ];
        }

        /**
         * Analyze test result
         * @param string $location Location where test results are stored
         * @return ReadableMap Collection of test analysis
         */
        public static function analyze(string $location): ReadableMap
        {
            $contents = scandir($location);
            $analysis = [];
            foreach ($contents as $file) {
                if (preg_match(TestResult::FILENAME_PATTERN, $file) === 1 && is_file($location . '/' . $file)) {
                    $analysis[] = self::parseFile($location . '/' . $file)->jsonSerialize();
                }
            }
            usort($analysis, fn($a, $b) => $a['timestamp'] <=> $b['timestamp']);
            return new ReadableMap($analysis);
        }

        private static function parseFile(string $file): ResultAnalysis
        {
            $fd = fopen($file, 'rb');
            $reading = false;
            $passed = $failed = $timestamp = $cases = null;
            while (($line = fgets($fd)) !== false) {
                if (!str_starts_with($line, '----')) {
                    $reading = true;
                }
                if (!$reading) {
                    continue;
                }
                if (str_starts_with($line, TestResult::KEYWORD_PASS)) {
                    $passed = self::parseLine($line);
                }
                if (str_starts_with($line, TestResult::KEYWORD_FAIL)) {
                    $failed = self::parseLine($line);
                }
                if (str_starts_with($line, TestResult::KEYWORD_TIMESTAMP)) {
                    $timestamp = (int) substr($line, strrpos($line, '-') + 1);
                }
                if (str_starts_with($line, TestResult::KEYWORD_CASES)) {
                    $cases = (int) substr($line, strrpos($line, '-') + 1);
                }
                if ($timestamp !== null && $passed !== null && $failed !== null && $cases !== null) {
                    break;
                }
            }
            fclose($fd);
            if ($passed === null) {
                throw new \Exception('Invalid file: ' . $file);
            }
            return new ResultAnalysis($timestamp, $cases, $passed, $failed);
        }

        private static function parseLine(string $line): int
        {
            $pos1 = strrpos($line, '-') + 1;
            $pos2 = strpos($line, '(');
//            $pos2 = strpos($line, '%');
            return (int) substr($line, $pos1, $pos2 - $pos1);
        }
    }

}
