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
     */
    final class CommandOptions implements \JsonSerializable
    {

        /** Whether verbose output is enabled (extra logging and details). */
        public readonly bool $quiet;

        /** Whether colored output should be disabled (plain text only). */
        public readonly bool $noColor;

        /**
         * Create a new CommandOptions instance.
         *
         * @param bool $quiet Enable verbose output (default false).
         * @param bool $noColor Disable colored output (default false).
         */
        public function __construct(bool $quiet, bool $noColor)
        {
            $this->quiet = $quiet;
            $this->noColor = $noColor;
        }

        /**
         * Serialize command options to JSON.
         *
         * @return array<string,mixed> Command options.
         */
        #[\Override]
        public final function jsonSerialize(): array
        {
            return [
                'quiet' => $this->quiet,
                'no_color' => $this->noColor,
            ];
        }
    }

}
