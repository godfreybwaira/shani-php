<?php

/**
 * Description of ConsolePrinter
 * @author coder
 *
 * Created on: Apr 9, 2025 at 4:59:02 PM
 */

namespace shani\core\log {

    enum ConsolePrinter: int
    {

        case COLOR_BLACK = 30;
        case COLOR_GREEN = 32;
        case COLOR_RED = 31;
        case COLOR_CYAN = 36;
        case COLOR_YELLOW = 33;
        case COLOR_BLUE = 34;
        case COLOR_WHITE = 37;
        case COLOR_MAGENTA = 35;

        public static function colorText(string $text, ConsolePrinter $textColor, ConsolePrinter $backgroundColor): string
        {
            $bg = ';' . (10 + $backgroundColor->value) . 'm';
            return "\033[0;{$textColor->value}{$bg}$text\033[0m";
        }
    }

}
