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
    use features\console\CommandRegistry;
    use features\console\helpers\HostName;
    use features\console\printer\ConsoleIO;

    final class RenameAliasCommand extends CommandContract
    {

        private readonly string $oldName;
        private readonly string $newName;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'rename:alias', 'old_name new_name', 'Rename an alias from old name to a new name', 'blog.com blog.co.tz');
        }

        public function execute(): void
        {
            $alias = AliasBuilder::fromAliasName($this->oldName);
            $alias->rename($this->newName, fn($s) => $this->registry->addResult($s));
        }

        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $this->oldName = ConsoleIO::read('What is the old alias name?', $this->validHostName);
                $this->newName = ConsoleIO::read('What is the new alias name?', $this->validHostName);
            } else {
                if (count($args) < 2) {
                    throw new \ArgumentCountError('Atleast two arguments are required.');
                }
                $this->oldName = HostName::create($args[0]);
                $this->newName = HostName::create($args[1]);
            }
            return $this->oldName . ' ' . $this->newName;
        }
    }

}
