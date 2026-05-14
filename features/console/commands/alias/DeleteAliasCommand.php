<?php

/**
 * Description of DeleteAliasCommand
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

    final class DeleteAliasCommand extends CommandContract
    {

        private readonly string $aliasName;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'delete:alias', 'alias_name', 'Delete an alias', 'blog.com');
        }

        public function execute(): void
        {
            $alias = AliasBuilder::fromAliasName($this->aliasName);
            $alias->delete(fn($s) => $this->registry->addResult($s));
        }

        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $this->aliasName = ConsoleIO::read('What is the alias name?', $this->validHostName);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 1) {
                    throw new \ArgumentCountError('Atleast one argument is required.');
                }
                $this->aliasName = HostName::create($values[0]);
            }
            return $this->aliasName;
        }
    }

}
