<?php

/**
 * Description of DeleteAliasCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\cli\commands {

    use features\cli\builders\AliasBuilder;
    use features\cli\CommandContract;

    final class DeleteAliasCommand extends CommandContract
    {

        private readonly string $aliasName;

        public function __construct()
        {
            parent::__construct('delete:alias', 'alias', 'Delete an alias', 'blog.com');
        }

        public function execute(): void
        {
            $alias = new AliasBuilder($this->aliasName);
            $alias->delete();
        }

        public function parse(string ...$args): CommandContract
        {
            $values = explode(self::SEPARATOR, $args[0]);
            if (count($values) < 1) {
                throw new \ArgumentCountError('Atleast one argument is required.');
            }
            $this->validateHostName($values[0]);
            $this->aliasName = $values[0];
            return $this;
        }
    }

}
