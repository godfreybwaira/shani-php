<?php

/**
 * Description of ConsoleInput
 * @author goddy
 *
 * @since May 5, 2026 at 6:06:56 PM
 */

namespace features\console\printer {

    /**
     * Class ConsoleInput
     *
     * Provides utility methods for interactive console input with validation.
     */
    final class ConsoleIO
    {

        /**
         * Prompt the user for text input via STDIN with validation.
         *
         * This method writes a prompt to STDOUT, reads user input from STDIN,
         * and validates the input using a custom closure. If validation fails,
         * it re-prompts the user with either the provided error message or the
         * original prompt until valid input is received.
         *
         * @param string   $text     The prompt message displayed to the user.
         *
         * @param \Closure $validator A closure that receives the input string and returns
         * a boolean indicating whether the input is valid. The signature of a validator
         * is <code>$validator(string $input):bool</code>
         *
         * @param string $onError Optional error message shown when validation fails.
         * If null, the original prompt message is reused.
         *
         * @return string|null The validated user input string, or null if no input was provided.
         *
         * @throws \RuntimeException If STDIN or STDOUT are unavailable.
         */
        public static function read(string $text, \Closure $validator, string $onError = null): ?string
        {
            self::output($text . ' ', newLine: false);
            while (true) {
                $input = trim(fgets(STDIN), PHP_EOL);
                if ($validator($input)) {
                    return $input;
                }
                self::output(($onError ?? 'That did not work! ' . $text) . ' ', newLine: false);
            }
        }

        /**
         * Write a message to the specified print stream.
         *
         * This method writes the given text to the provided stream (e.g. STDOUT or STDERR),
         * optionally appending a newline at the end. By default, it writes to the standard
         * output stream.
         *
         * @param string $text   The message to output.
         * @param resource $stream The target stream to write to.
         * @param bool   $newLine Whether to append a newline character (default true).
         *
         * @return void
         *
         * @throws \RuntimeException If the stream resource is invalid or unavailable.
         *
         */
        private static function write(string $text, $stream, bool $newLine = true): void
        {
            fwrite($stream, $text . ($newLine ? PHP_EOL : ''));
        }

        /**
         * Write a message to the standard output stream.
         *
         * This method writes the given text to the STDOUT stream, optionally
         * appending a newline at the end.
         *
         * @param string $text   The message to output.
         * @param bool   $newLine Whether to append a newline character (default true).
         *
         * @return void
         *
         * @throws \RuntimeException If the stream resource is invalid or unavailable.
         *
         */
        public static function output(string $text, bool $newLine = true): void
        {
            self::write($text, STDOUT, $newLine);
        }

        /**
         * Write a message to the standard error stream.
         *
         * This method writes the given text to the STDERR stream, optionally
         * appending a newline at the end.
         *
         * @param string $text   The message to output.
         * @param bool   $newLine Whether to append a newline character (default true).
         *
         * @return void
         *
         * @throws \RuntimeException If the stream resource is invalid or unavailable.
         *
         */
        public static function error(string $text, bool $newLine = true): void
        {
            self::write($text, STDERR, $newLine);
        }

        /**
         * Write a message to the standard input stream.
         *
         * This method writes the given text to the STDIN stream, optionally
         * appending a newline at the end.
         *
         * @param string $text   The message to output.
         * @param bool   $newLine Whether to append a newline character (default true).
         *
         * @return void
         *
         * @throws \RuntimeException If the stream resource is invalid or unavailable.
         *
         */
        public static function input(string $text, bool $newLine = true): void
        {
            self::write($text, STDIN, $newLine);
        }
    }

}
