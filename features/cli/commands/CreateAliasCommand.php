<?php

/**
 * Description of CreateAliasCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\cli\commands {

    use features\cli\builders\AliasBuilder;
    use features\cli\CommandContract;

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
            $values = explode(self::SEPARATOR, $args[0]);
            if (count($values) < 2) {
                throw new \ArgumentCountError('Atleast two arguments are required.');
            }
            $this->validateIdentifier($values[0]);
            $this->validateHostName($values[1]);
            $this->aliasName = $values[0];
            $this->hostname = $values[1];
            return $this;
        }
    }

}
