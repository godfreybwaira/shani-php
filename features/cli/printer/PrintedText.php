<?php

/**
 * Description of PrintedText
 * @author goddy
 *
 * Created on: May 4, 2026 at 6:41:51 PM
 */

namespace features\cli\printer {

    final class PrintedText
    {

        public readonly string $text;
        public readonly ConsoleColor $primaryColor;
        public readonly ?ConsoleColor $secondaryColor;

        public function __construct(string $text, ConsoleColor $primaryColor, ?ConsoleColor $secondaryColor = null)
        {
            $this->text = $text;
            $this->primaryColor = $primaryColor;
            $this->secondaryColor = $secondaryColor;
        }

//        public static function colorText(string $text, ConsoleColor $textColor, ConsoleColor $backgroundColor = null): string
//        {
//            $bgcolor = $backgroundColor?->value ?? self::COLOR_BLACK->value;
//            $bg = ';' . (10 + $bgcolor) . 'm';
//            return "\033[0;{$textColor->value}{$bg}$text\033[0m";
//        }
    }

}
