<?php

/**
 * Description of PrintStream
 * @author goddy
 *
 * Created on: May 5, 2026 at 5:21:49 PM
 */

namespace features\console\printer {

    /**
     * Enum PrintStream
     *
     * Represents the available console streams (STDIN, STDOUT, STDERR).
     * Provides a helper method to retrieve the underlying PHP stream resource.
     */
    enum PrintStream
    {

        /** Standard error stream (STDERR). */
        case ERROR_STREAM;

        /** Standard input stream (STDIN). */
        case INPUT_STREAM;

        /** Standard output stream (STDOUT). */
        case OUTPUT_STREAM;

        /**
         * Get the underlying PHP stream resource for the enum case.
         *
         * @return resource The corresponding PHP stream (STDIN, STDOUT, or STDERR).
         *
         * @throws \RuntimeException If the stream is unavailable or not defined.
         *
         */
        public function getStream(): mixed
        {
            return match ($this) {
                PrintStream::INPUT_STREAM => STDIN,
                PrintStream::ERROR_STREAM => STDERR,
                PrintStream::OUTPUT_STREAM => STDOUT,
            };
        }
    }

}
