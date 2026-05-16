<?php

/**
 * Command to list all aliases for a given host.
 *
 * This command retrieves all aliases associated with a specific host
 * and displays them in a numbered list along with their corresponding
 * virtual host. It can be executed interactively (via console prompts)
 * or by passing arguments directly in the format "hostname".
 *
 * If no aliases are found for the given host, an error will be thrown.
 *
 * @author goddy
 * @created May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\alias {

    use features\console\builders\AliasBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\Formatter;
    use features\console\helpers\HostName;
    use features\console\ResourceSelector;

    final class ListHostAliasCommand extends CommandContract
    {

        /**
         * The host name for which aliases will be listed.
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
            parent::__construct(
                    $registry,
                    'list:alias',
                    'hostname',
                    'Show all available host aliases',
                    'localhost'
            );
        }

        /**
         * Executes the alias listing operation.
         *
         * Uses the {@see AliasBuilder} to retrieve all aliases for the given host.
         * Each alias is formatted and added to the registry. If no aliases are found,
         * an exception is thrown.
         *
         * @return void
         *
         * @throws \InvalidArgumentException If no aliases are found for the host.
         */
        public function execute(): void
        {
            $aliases = AliasBuilder::getAllByHostName($this->hostName);
            if (!$aliases->valid()) {
                throw new \InvalidArgumentException('No alias found for host "' . $this->hostName . '"');
            }

            $this->registry->addResult(Formatter::formatSentence('#. ALIAS', 'HOST', separator: ' '));
            foreach ($aliases as $idx => $alias) {
                $message = ($idx + 1) . '. ' . $alias->aliasName;
                $this->registry->addResult(Formatter::formatSentence($message, $alias->vhost->metadata->hostName));
            }
        }

        /**
         * Parses command arguments or prompts the user interactively.
         *
         * - If no arguments are provided, prompts the user to select a host interactively.
         * - If arguments are provided, expects the format "hostname".
         *
         * @param string ...$args The command arguments (hostname).
         *
         * @return string|null The host name or null if skipped.
         *
         * @throws \ArgumentCountError If fewer than one argument is provided.
         */
        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $this->hostName = (new ResourceSelector())->selectHost();
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 1) {
                    throw new \ArgumentCountError('At least one argument is required.');
                }
                $this->hostName = HostName::create($values[0]);
            }
            return $this->hostName;
        }
    }

}
