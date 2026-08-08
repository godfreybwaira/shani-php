<?php

/**
 * Description of CustomCommandsInterface
 * @author goddy
 *
 * Created on: Aug 8, 2026 at 11:51:32 AM
 */

namespace features\console\commands {

    interface CustomCommandsInterface
    {

        /**
         * Call a custom user command
         * @param string $label command label to decide which custom command to call
         * @return bool Return true if command executed successfully, false otherwise
         */
        public function callCommand(string $label): bool;
    }

}
