<?php

/**
 * Description of CreateAliasCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\alias {

    use features\console\builders\AliasBuilder;
    use features\console\CommandContract;
    use features\console\printer\ConsoleIO;

    final class CreateAliasCommand extends CommandContract
    {

        private readonly string $aliasName;
        private readonly string $hostname;

        public function __construct()
        {
            parent::__construct('create:alias', 'alias@hostname', 'Create a host alias', 'blog.com@localhost');
        }

        public function execute(): void
        {
            $alias = new AliasBuilder($this->aliasName, $this->hostname);
            $alias->build();
        }

        public function parse(string ...$args): CommandContract
        {
            if (empty($args)) {
                $this->aliasName = ConsoleIO::input('What is the alias name?', $this->validHostName);
                $this->hostname = ConsoleIO::input('What is the host name?', $this->validHostName);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 2) {
                    throw new \ArgumentCountError('Atleast two arguments are required.');
                }
                self::validateHostName($values[0]);
                self::validateHostName($values[1]);
                $this->aliasName = $values[0];
                $this->hostname = $values[1];
            }
            return $this;
        }
    }

}
