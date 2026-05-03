<?php

/**
 * Description of LightBuilderInterface
 * @author goddy
 *
 * Created on: May 2, 2026 at 2:42:51 PM
 */

namespace features\cli\builders {

    interface LightBuilderInterface
    {

        /**
         * Check if the resource exists
         * @return bool True if exists, false otherwise.
         */
        public function exists(): bool;

        /**
         * Build the resource if it does not exists. If it does, nothing will be done.
         * @return self
         */
        public function build(): self;
    }

}
