<?php

/**
 * Test result analysis
 * @author coder
 *
 * Created on: May 5, 2025 at 10:32:52 AM
 */

namespace test {

    use lib\map\ReadableMap;

    final class ResultAnalysis implements \JsonSerializable
    {

        private readonly int $timestamp;
        private readonly float $passed, $failed;

        private function __construct(int $timestamp, float $passed, float $failed)
        {
            $this->timestamp = $timestamp;
            $this->passed = $passed;
            $this->failed = $failed;
        }

        public function jsonSerialize(): array
        {
            return[
                'timestamp' => $this->timestamp,
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
            $passed = $failed = $timestamp = null;
            while (($line = fgets($fd)) !== false) {
                if (!str_starts_with($line, '----')) {
                    $reading = true;
                }
                if (!$reading) {
                    continue;
                }
                if (str_starts_with($line, TestResult::KEYWORD_PASS)) {
                    $passed = self::getValue($line);
                }
                if (str_starts_with($line, TestResult::KEYWORD_FAIL)) {
                    $failed = self::getValue($line);
                }
                if (str_starts_with($line, TestResult::KEYWORD_TIMESTAMP)) {
                    $timestamp = (int) substr($line, strrpos($line, '-') + 1);
                }
                if ($timestamp !== null && $passed !== null && $failed !== null) {
                    break;
                }
            }
            fclose($fd);
            if ($passed === null) {
                throw new \Exception('Invalid file: ' . $file);
            }
            return new ResultAnalysis($timestamp, $passed, $failed);
        }

        private static function getValue(string $line): float
        {
            $pos1 = strpos($line, '(') + 1;
            $pos2 = strpos($line, '%');
            return (float) substr($line, $pos1, $pos2 - $pos1);
        }
    }

}
