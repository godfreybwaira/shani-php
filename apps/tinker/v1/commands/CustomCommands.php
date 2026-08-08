<?php

namespace apps\tinker\v1\commands {

    use features\console\commands\CustomCommandsInterface;

    final class CustomCommands implements CustomCommandsInterface
    {

        #[\Override]
        public function callCommand(string $label): bool
        {
            //TODO implement custom command call based on a given $label.
            //Return true if command executed successfully, false otherwise
            return true;
        }
    }

}

