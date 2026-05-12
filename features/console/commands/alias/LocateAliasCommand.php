<?php

/**
 * Description of LocateAliasCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\alias {

    use features\console\builders\AliasBuilder;
    use features\console\CommandContract;
    use features\console\printer\ConsoleIO;

    final class LocateAliasCommand extends CommandContract
    {

        private readonly string $aliasName;

        public function __construct()
        {
            parent::__construct('locate:alias', 'alias', 'Show the full path to an existing virtual host alias', 'blog.com');
        }

        public function execute(): void
        {
            $alias = AliasBuilder::fromName($this->aliasName);
            $alias?->locate();
        }

        public function parse(string ...$args): CommandContract
        {
            if (empty($args)) {
                $this->aliasName = ConsoleIO::read('What is the alias name?', $this->validHostName);
            } else {
                if (count($args) < 1) {
                    throw new \ArgumentCountError('Atleast one argument is allowed.');
                }
                self::validateHostName($args[0]);
                $this->aliasName = $args[0];
            }
            return $this;
        }
    }

}
