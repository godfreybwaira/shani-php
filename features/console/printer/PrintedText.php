<?php

/**
 * Description of PrintedText
 * @author goddy
 *
 * Created on: May 4, 2026 at 6:41:51 PM
 */

namespace features\console\printer {

    /**
     * Class PrintedText
     *
     * Represents a piece of text with optional console coloring.
     * Stores both the plain text and its colored variant using ANSI escape codes.
     * Provides factory methods for common message types (success, error, warning, info).
     */
    final class PrintedText implements \Stringable
    {

        /** The original plain text without formatting. */
        public readonly string $plainText;

        /** The console color applied to the text. */
        public readonly ConsoleColor $color;

        /** The text wrapped with ANSI color codes and reset sequence. */
        public readonly string $coloredText;

        /**
         * Private constructor to enforce factory usage.
         *
         * @param string       $text  The plain text message.
         * @param ConsoleColor $color The console color to apply.
         */
        private function __construct(string $text, ConsoleColor $color)
        {
            $this->plainText = $text;
            $this->color = $color;
            $this->coloredText = $color->value . $text . ConsoleColor::RESET->value;
        }

        /**
         * Create a success message (bold green).
         *
         * @param string $text The message text.
         * @return PrintedText
         */
        public static function success(string $text): PrintedText
        {
            return new PrintedText($text, ConsoleColor::BOLD_GREEN);
        }

        /**
         * Create an error message (bold red).
         *
         * @param string $text The message text.
         * @return PrintedText
         */
        public static function error(string $text): PrintedText
        {
            return new PrintedText($text, ConsoleColor::BOLD_RED);
        }

        /**
         * Create a warning message (bold yellow).
         *
         * @param string $text The message text.
         * @return PrintedText
         */
        public static function warning(string $text): PrintedText
        {
            return new PrintedText($text, ConsoleColor::BOLD_YELLOW);
        }

        /**
         * Create an info message (bold blue).
         *
         * @param string $text The message text.
         * @return PrintedText
         */
        public static function info(string $text): PrintedText
        {
            return new PrintedText($text, ConsoleColor::BOLD_BLUE);
        }

        /**
         * Bold text.
         *
         * @param string $text The message text.
         * @return PrintedText
         */
        public static function bold(string $text): PrintedText
        {
            return new PrintedText($text, ConsoleColor::BOLD);
        }

        /**
         * Create a custom colored message.
         *
         * @param string       $text  The message text.
         * @param ConsoleColor $color The console color to apply.
         * @return PrintedText
         */
        public static function color(string $text, ConsoleColor $color): PrintedText
        {
            return new PrintedText($text, $color);
        }

        /**
         * Convert the object to a string.
         *
         * Returns the colored text representation so the object
         * can be directly echoed or concatenated.
         *
         * @return string
         */
        #[\Override]
        public function __toString(): string
        {
            return $this->coloredText;
        }
    }

}
