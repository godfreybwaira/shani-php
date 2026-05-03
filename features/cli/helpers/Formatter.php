<?php

/**
 * Description of Formatter
 * @author goddy
 *
 * Created on: May 3, 2026 at 3:55:59 PM
 */

namespace features\cli\helpers {

    final class Formatter
    {

        public static function formatSentence(string $inputText, string $resultText, int $sentenceWidth = 100, string $separator = '.'): string
        {
            $inputLength = strlen($inputText);
            $resultLength = strlen($resultText);
            $multiplier = $sentenceWidth - ($inputLength + $resultLength + 2);
            return $inputText . ' ' . str_repeat($separator, $multiplier) . ' ' . $resultText . PHP_EOL;
        }

        public static function placeCenter(string $inputText, bool $underline = false, int $sentenceWidth = 100, string $separator = ' '): string
        {
            $inputSize = strlen($inputText);
            $multiplier = floor(($sentenceWidth - ($inputSize + 2)) / 2);
            $content = null;
            if ($underline) {
                $padding = str_repeat(' ', $multiplier);
                $content = $padding . ' ' . str_repeat('-', $inputSize) . ' ' . $padding . PHP_EOL;
            }
            $paddingTexts = str_repeat($separator, $multiplier);
            return $paddingTexts . ' ' . $inputText . ' ' . $paddingTexts . PHP_EOL . $content;
        }

        public static function placeLeft(string $inputText, bool $underline = false, int $sentenceWidth = 100, string $separator = ' '): string
        {
            $inputSize = strlen($inputText);
            $multiplier = $sentenceWidth - ($inputSize + 1);
            $content = null;
            if ($underline) {
                $padding = str_repeat(' ', $multiplier);
                $content = str_repeat('-', $inputSize) . ' ' . $padding . PHP_EOL;
            }
            $paddingTexts = str_repeat($separator, $multiplier);
            return $inputText . ' ' . $paddingTexts . PHP_EOL . $content;
        }

        public static function placeRight(string $inputText, bool $underline = false, int $sentenceWidth = 100, string $separator = ' '): string
        {
            $inputSize = strlen($inputText);
            $multiplier = $sentenceWidth - ($inputSize + 1);
            $content = null;
            if ($underline) {
                $padding = str_repeat(' ', $multiplier);
                $content = $padding . ' ' . str_repeat('-', $inputSize) . PHP_EOL;
            }
            $paddingTexts = str_repeat($separator, $multiplier);
            return $paddingTexts . ' ' . $inputText . PHP_EOL . $content;
        }
    }

}
