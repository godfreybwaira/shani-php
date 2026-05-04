<?php

/**
 * Description of PrinterColor
 * @author coder
 *
 * Created on: Apr 9, 2025 at 4:59:02 PM
 */

namespace features\cli\printer {

    enum ConsoleColor: int
    {

        case COLOR_BLACK = 30;
        case COLOR_GREEN = 32;
        case COLOR_RED = 31;
        case COLOR_CYAN = 36;
        case COLOR_YELLOW = 33;
        case COLOR_BLUE = 34;
        case COLOR_WHITE = 37;
        case COLOR_MAGENTA = 35;
    }

}
