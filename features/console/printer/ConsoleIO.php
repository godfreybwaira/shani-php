<?php

/**
 * Description of ConsoleInput
 * @author goddy
 *
 * Created on: May 5, 2026 at 6:06:56 PM
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
         * @param string|null $onError Optional error message shown when validation fails.
         * If null, the original prompt message is reused.
         *
         * @param \Closure $validator A closure that receives the input string and returns
         * a boolean indicating whether the input is valid. The signature of validator is
         * <code>$validator(string $input):bool</code>
         *
         *
         * @return string|null The validated user input string, or null if no input was provided.
         *
         * @throws \RuntimeException If STDIN or STDOUT are unavailable.
         */
        public static function input(string $text, ?string $onError, \Closure $validator): ?string
        {
            fwrite(STDOUT, $text . ' ');
            while (true) {
                $input = trim(fgets(STDIN), PHP_EOL);
                if ($validator($input)) {
                    return $input;
                }
                fwrite(STDOUT, ($onError ?? $text) . ' ');
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
         * @param bool   $newLine Whether to append a newline character (default true).
         * @param PrintStream $stream The target stream to write to. Defaults to
         *                            PrintStream::OUTPUT_STREAM.
         *
         * @return void
         *
         * @throws \RuntimeException If the stream resource is invalid or unavailable.
         *
         */
        public static function output(string $text, bool $newLine = true, PrintStream $stream = PrintStream::OUTPUT_STREAM): void
        {
            fwrite($stream->getStream(), $text . ($newLine ? PHP_EOL : ''));
        }
    }

}
