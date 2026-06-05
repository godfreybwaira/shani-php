<?php

/**
 * Description of ConsoleColor
 * @author coder
 *
 * @since Apr 9, 2025 at 4:59:02 PM
 */

namespace features\console\printer {

    /**
     * Enum ConsoleColor
     *
     * Defines ANSI escape codes for console text coloring.
     * Includes regular and bold variants for common colors,
     * plus a reset option to restore default terminal formatting.
     *
     */
    enum ConsoleColor: string
    {

        /** No color at all */
        case NONE = '';

        /** Reset to default terminal color. */
        case RESET = "\033[0m";

        /** Bold text */
        case BOLD = "\033[1m";

        // Regular Colors

        /** Black text. */
        case BLACK = "\033[0;30m";

        /** Red text. */
        case RED = "\033[0;31m";

        /** Green text. */
        case GREEN = "\033[0;32m";

        /** Yellow text. */
        case YELLOW = "\033[0;33m";

        /** Blue text. */
        case BLUE = "\033[0;34m";

        /** Purple text. */
        case PURPLE = "\033[0;35m";

        /** Cyan text. */
        case CYAN = "\033[0;36m";

        /** White text. */
        case WHITE = "\033[0;37m";

        // Bold Colors

        /** Bold red text. */
        case BOLD_RED = "\033[1;31m";

        /** Bold green text. */
        case BOLD_GREEN = "\033[1;32m";

        /** Bold yellow text. */
        case BOLD_YELLOW = "\033[1;33m";

        /** Bold blue text. */
        case BOLD_BLUE = "\033[1;34m";
    }

}
