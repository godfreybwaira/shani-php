<?php

/**
 * Description of CommandOptions
 * @author goddy
 *
 * Created on: May 5, 2026 at 3:34:39 PM
 */

namespace features\console {

    final class CommandOptions
    {

        public readonly bool $verbose;
        public readonly bool $noColor;

        public function __construct(bool $verbose, bool $noColor)
        {
            $this->verbose = $verbose;
            $this->noColor = $noColor;
        }
    }

}
