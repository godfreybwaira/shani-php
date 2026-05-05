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

    final class RenameAliasCommand extends CommandContract
    {

        private readonly string $oldName;
        private readonly string $newName;

        public function __construct()
        {
            parent::__construct('alias:rename', 'old_name new_name', 'Rename an alias from old name to a new name', 'blog.com blog.co.tz');
        }

        public function execute(): void
        {
            $alias = new AliasBuilder($this->oldName);
            $alias->rename($this->newName);
        }

        public function parse(string ...$args): CommandContract
        {
            if (count($args) < 2) {
                throw new \ArgumentCountError('Atleast two argument is required.');
            }
            $this->validateHostName($args[0]);
            $this->validateHostName($args[1]);
            $this->oldName = $args[0];
            $this->newName = $args[1];
            return $this;
        }
    }

}
