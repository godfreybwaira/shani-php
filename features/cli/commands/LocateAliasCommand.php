<?php

/**
 * Description of LocateAliasCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\cli\commands {

    use features\cli\builders\AliasBuilder;
    use features\cli\CommandContract;

    final class LocateAliasCommand extends CommandContract
    {

        private readonly string $aliasName;

        public function __construct()
        {
            parent::__construct('locate:alias', 'alias', 'Show the full path to an existing virtual host alias', 'blog.com');
        }

        public function execute(): void
        {
            $alias = new AliasBuilder($this->aliasName);
            $alias->locate();
        }

        public function parse(string ...$args): CommandContract
        {
            if (count($args) > 1) {
                throw new \ArgumentCountError('Only one argument is required.');
            }
            $this->validateHostName($args[0]);
            $this->aliasName = $args[0];
            return $this;
        }
    }

}
