<?php

/**
 * Description of RenameAliasCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\alias {

    use features\console\builders\AliasBuilder;
    use features\console\CommandContract;
    use features\console\printer\ConsoleIO;

    final class RenameAliasCommand extends CommandContract
    {

        private readonly string $oldName;
        private readonly string $newName;

        public function __construct()
        {
            parent::__construct('rename:alias', 'old_name new_name', 'Rename an alias from old name to a new name', 'blog.com blog.co.tz');
        }

        public function execute(): void
        {
            $alias = new AliasBuilder($this->oldName);
            $alias->rename($this->newName);
        }

        public function parse(string ...$args): CommandContract
        {
            if (empty($args)) {
                $this->oldName = ConsoleIO::input('What is the old alias name?', $this->validHostName);
                $this->newName = ConsoleIO::input('What is the new alias name?', $this->validHostName);
            } else {
                if (count($args) < 2) {
                    throw new \ArgumentCountError('Atleast two argument is required.');
                }
                $this->validateHostName($args[0]);
                $this->validateHostName($args[1]);
                $this->oldName = $args[0];
                $this->newName = $args[1];
            }
            return $this;
        }
    }

}
