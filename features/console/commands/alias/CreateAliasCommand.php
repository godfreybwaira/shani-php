<?php

/**
 * Command to create a new host alias.
 *
 * This command generates a virtual host alias inside a specified project host.
 * It can be executed interactively (via console prompts) or by passing arguments
 * directly in the format "alias@hostname".
 *
 * If the host does not exist or the alias name is invalid, an error will be thrown.
 *
 * @author goddy
 * @created May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\alias {

    use features\console\builders\AliasBuilder;
    use features\console\builders\VirtualHostBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\HostName;
    use features\console\printer\ConsoleIO;
    use features\console\ResourceSelector;

    final class CreateAliasCommand extends CommandContract
    {

        /**
         * The alias name to be created.
         *
         * @var string
         */
        private readonly string $aliasName;

        /**
         * The host name to which the alias will be linked.
         *
         * @var string
         */
        private readonly string $hostName;

        /**
         * Initializes the command with its registry and metadata.
         *
         * @param CommandRegistry $registry The command registry instance.
         */
        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'create:alias', 'alias@hostname', 'Create a host alias', 'blog.com@localhost');
        }

        /**
         * Executes the alias creation operation.
         *
         * Uses the {@see VirtualHostBuilder} to build the host and
         * {@see AliasBuilder} to generate the new alias. The result is logged
         * in the registry.
         *
         * @return void
         */
        public function execute(): void
        {
            $vhost = VirtualHostBuilder::fromHostName($this->hostName);
            $alias = new AliasBuilder($vhost, $this->aliasName);
            $alias->build(fn($s) => $this->registry->addResult($s));
        }

        /**
         * Parses command arguments or prompts the user interactively.
         *
         * - If no arguments are provided, prompts the user to enter an alias name
         *   and select a host interactively.
         * - If arguments are provided, expects the format "alias@hostname".
         *   Splits the string into alias name and host name.
         *
         * @param string ...$args The command arguments (alias@hostname).
         *
         * @return string|null A string containing "alias@hostname" or null if skipped.
         *
         * @throws \ArgumentCountError If fewer than two arguments are provided.
         */
        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $this->aliasName = ConsoleIO::read('What is the alias name?', $this->validHostName);
                $this->hostName = (new ResourceSelector())->selectHost();
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 2) {
                    throw new \ArgumentCountError('At least two arguments are required.');
                }
                $this->aliasName = HostName::create($values[0]);
                $this->hostName = HostName::create($values[1]);
            }
            return $this->aliasName . self::SEPARATOR . $this->hostName;
        }
    }

}
