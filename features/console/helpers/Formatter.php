<?php

/**
 * Description of Formatter
 * @author goddy
 *
 * Created on: May 3, 2026 at 3:55:59 PM
 */

namespace features\console\helpers {

    /**
     * Class Formatter
     *
     * Provides utility methods for formatting text output in CLI applications.
     * Includes helpers for aligning text (center, left, right), formatting sentences,
     * and trimming suffixes.
     *
     * @example
     * ```php
     * echo Formatter::formatSentence("Project", "Created");
     * echo Formatter::placeCenter("Welcome", underline: true);
     * echo Formatter::placeLeft("Error", underline: true);
     * echo Formatter::placeRight("Success");
     * echo Formatter::trimSuffix("ControllerService", "Service");
     * ```
     */
    final class Formatter
    {

        /**
         * Format a sentence with a separator filling the gap between input and result.
         *
         * Example: "Project .... Created"
         *
         * @param string $inputText   The left-hand text.
         * @param string $resultText  The right-hand text.
         * @param int    $sentenceWidth Total width of the formatted sentence (default 100).
         * @param string $separator   Character(s) used to fill the gap (default '.').
         *
         * @return string The formatted sentence with separators.
         */
        public static function formatSentence(string $inputText, string $resultText, int $sentenceWidth = 100, string $separator = '.'): string
        {
            $inputLength = mb_strlen($inputText);
            $resultLength = mb_strlen($resultText);
            $multiplier = $sentenceWidth - ($inputLength + $resultLength + 2);
            return $inputText . ' ' . str_repeat($separator, $multiplier) . ' ' . $resultText . PHP_EOL;
        }

        /**
         * Place text centered within a given width.
         *
         * Optionally underlines the text with dashes.
         *
         * @param string $inputText   The text to center.
         * @param bool   $underline   Whether to underline the text (default false).
         * @param int    $sentenceWidth Total width of the line (default 100).
         * @param string $separator   Character(s) used for padding (default space).
         *
         * @return string The centered text with optional underline.
         */
        public static function placeCenter(string $inputText, bool $underline = false, int $sentenceWidth = 100, string $separator = ' '): string
        {
            $inputSize = mb_strlen($inputText);
            $multiplier = floor(($sentenceWidth - ($inputSize + 2)) / 2);
            $content = null;
            if ($underline) {
                $padding = str_repeat(' ', $multiplier);
                $content = $padding . ' ' . str_repeat('-', $inputSize) . ' ' . $padding . PHP_EOL;
            }
            $paddingTexts = str_repeat($separator, $multiplier);
            return $paddingTexts . ' ' . $inputText . ' ' . $paddingTexts . PHP_EOL . $content;
        }

        /**
         * Place text aligned to the left within a given width.
         *
         * Optionally underlines the text with dashes.
         *
         * @param string $inputText   The text to align left.
         * @param bool   $underline   Whether to underline the text (default false).
         * @param int    $sentenceWidth Total width of the line (default 100).
         * @param string $separator   Character(s) used for padding (default space).
         *
         * @return string The left-aligned text with optional underline.
         */
        public static function placeLeft(string $inputText, bool $underline = false, int $sentenceWidth = 100, string $separator = ' '): string
        {
            $inputSize = mb_strlen($inputText);
            $multiplier = $sentenceWidth - ($inputSize + 1);
            $content = null;
            if ($underline) {
                $padding = str_repeat(' ', $multiplier);
                $content = str_repeat('-', $inputSize) . ' ' . $padding . PHP_EOL;
            }
            $paddingTexts = str_repeat($separator, $multiplier);
            return $inputText . ' ' . $paddingTexts . PHP_EOL . $content;
        }

        /**
         * Place text aligned to the right within a given width.
         *
         * Optionally underlines the text with dashes.
         *
         * @param string $inputText   The text to align right.
         * @param bool   $underline   Whether to underline the text (default false).
         * @param int    $sentenceWidth Total width of the line (default 100).
         * @param string $separator   Character(s) used for padding (default space).
         *
         * @return string The right-aligned text with optional underline.
         */
        public static function placeRight(string $inputText, bool $underline = false, int $sentenceWidth = 100, string $separator = ' '): string
        {
            $inputSize = mb_strlen($inputText);
            $multiplier = $sentenceWidth - ($inputSize + 1);
            $content = null;
            if ($underline) {
                $padding = str_repeat(' ', $multiplier);
                $content = $padding . ' ' . str_repeat('-', $inputSize) . PHP_EOL;
            }
            $paddingTexts = str_repeat($separator, $multiplier);
            return $paddingTexts . ' ' . $inputText . PHP_EOL . $content;
        }

        /**
         * Trim a suffix from a string if present (case-insensitive).
         *
         * @param string $value  The input string.
         * @param string $suffix The suffix to remove.
         *
         * @return string The string without the suffix, or unchanged if not present.
         *
         * @example
         * ```php
         * echo Formatter::trimSuffix("ControllerService", "Service"); // "Controller"
         * ```
         */
        public static function trimSuffix(string $value, string $suffix): string
        {
            $name = strtolower($value);
            $str = strtolower($suffix);
            return str_ends_with($name, $str) ? substr($value, 0, strpos($name, $str)) : $value;
        }

        /**
         * Render a table with borders from an array of rows.
         *
         * Each row is an array of string values. Column widths are automatically
         * calculated based on the longest value in each column. Borders are drawn
         * around the table and between rows.
         *
         * @param array<int,array<int,string>> $rows   The table rows (each row is an array of columns).
         * @param array<int,string>|null       $headers Optional headers for the table.
         *
         * @return string The formatted table with borders.
         *
         * @example
         * ```php
         * $rows = [
         *     ["1", "Project Alpha", "Active"],
         *     ["2", "Project Beta", "Completed"],
         * ];
         * $headers = ["ID", "Name", "Status"];
         * echo Formatter::table($rows, $headers);
         * ```
         */
        public static function table(array $rows, ?array $headers = null): string
        {
            if (empty($rows) && empty($headers)) {
                return '';
            }

            // 1. Calculate max width for each column efficiently
            $colWidths = [];
            $processRow = function (array $row) use (&$colWidths) {
                foreach (array_values($row) as $i => $cell) {
                    // mb_strlen handles UTF-8 characters correctly
                    $length = mb_strlen((string) $cell, 'UTF-8');
                    $colWidths[$i] = max($colWidths[$i] ?? 0, $length);
                }
            };

            if ($headers) {
                $processRow($headers);
            }
            foreach ($rows as $row) {
                $processRow($row);
            }

            // 2. Multibyte-safe padding helper (or use mb_str_pad in PHP 8.3+)
            $mbStrPad = function (string $input, int $padLength): string {
                $diff = $padLength - mb_strlen($input, 'UTF-8');
                return $input . str_repeat(' ', max(0, $diff));
            };

            // 3. Helper to draw horizontal borders
            $drawBorder = function () use ($colWidths): string {
                $parts = array_map(fn($w) => str_repeat('-', $w + 2), $colWidths);
                return '+' . implode('+', $parts) . '+' . PHP_EOL;
            };

            // 4. Helper to format a row
            $formatRow = function (array $row) use ($colWidths, $mbStrPad): string {
                $cells = [];
                foreach (array_values($row) as $i => $cell) {
                    $width = $colWidths[$i] ?? 0;
                    $cells[] = ' ' . $mbStrPad((string) $cell, $width) . ' ';
                }
                return '|' . implode('|', $cells) . '|' . PHP_EOL;
            };

            // 5. Assemble Output
            $output = $drawBorder();

            if ($headers) {
                $output .= $formatRow($headers);
                $output .= $drawBorder();
            }

            foreach ($rows as $row) {
                $output .= $formatRow($row);
            }

            return $output . $drawBorder();
        }
    }

}
