<?php

/**
 * Description of CommandOptions
 * @author goddy
 *
 * Created on: May 5, 2026 at 3:34:39 PM
 */

namespace features\console {

    /**
     * Class CommandOptions
     *
     * Represents global options that affect command execution behavior.
     * Used to configure verbosity and color output in CLI applications.
     *
     * @example
     * ```php
     * $options = new CommandOptions(verbose: true, noColor: false);
     *
     * if ($options->verbose) {
     *     echo "Detailed logs enabled\n";
     * }
     *
     * if ($options->noColor) {
     *     echo "Plain text output only\n";
     * }
     * ```
     */
    final class CommandOptions
    {

        /** Whether verbose output is enabled (extra logging and details). */
        public readonly bool $verbose;

        /** Whether colored output should be disabled (plain text only). */
        public readonly bool $noColor;

        /**
         * Create a new CommandOptions instance.
         *
         * @param bool $verbose Enable verbose output (default false).
         * @param bool $noColor Disable colored output (default false).
         */
        public function __construct(bool $verbose, bool $noColor)
        {
            $this->verbose = $verbose;
            $this->noColor = $noColor;
        }
    }

}
