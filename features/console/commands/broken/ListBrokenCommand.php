<?php

/**
 * Command to list broken objects in the project.
 *
 * This command identifies and lists broken virtual hosts and aliases
 * that are not properly connected to their dependents. It helps developers
 * quickly detect misconfigured or incomplete resources.
 *
 * If no broken objects are found, the output will display "(Empty)".
 *
 * @author goddy
 * @created May 15, 2026 at 8:59:40 AM
 */

namespace features\console\commands\broken {

    use features\console\builders\AliasBuilder;
    use features\console\builders\VirtualHostBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\Formatter;

    final class ListBrokenCommand extends CommandContract
    {

        /**
         * Initializes the command with its registry and metadata.
         *
         * @param CommandRegistry $registry The command registry instance.
         */
        public function __construct(CommandRegistry $registry)
        {
            parent::__construct(
                    $registry,
                    'list:broken',
                    null,
                    'List broken objects that has not connected to their dependents',
                    null
            );
        }

        /**
         * Executes the broken object listing operation.
         *
         * Calls helper methods to list broken virtual hosts and aliases.
         *
         * @return void
         */
        public function execute(): void
        {
            $this->brokenVhosts();
            $this->registry->addResult(PHP_EOL);
            $this->brokenAliases();
        }

        /**
         * Lists broken aliases.
         *
         * Retrieves all broken aliases using {@see AliasBuilder::getAllBroken()}
         * and formats them for display. If none are found, displays "(Empty)".
         *
         * @return void
         */
        private function brokenAliases(): void
        {
            $this->registry->addResult(Formatter::placeLeft('ALIASES', true));
            $aliases = AliasBuilder::getAllBroken();
            $index = 1;

            foreach ($aliases as $aliasPath) {
                $message = ($index++) . '. ' . basename($aliasPath, '.alias');
                $this->registry->addResult(Formatter::formatSentence($message, 'Broken'));
            }

            if ($index === 1) {
                $this->registry->addResult('(Empty)');
            }
        }

        /**
         * Lists broken virtual hosts.
         *
         * Retrieves all broken virtual hosts using {@see VirtualHostBuilder::getAllBroken()}
         * and formats them for display. If none are found, displays "(Empty)".
         *
         * @return void
         */
        private function brokenVhosts(): void
        {
            $this->registry->addResult(Formatter::placeLeft('VIRTUAL HOSTS', true));
            $vhosts = VirtualHostBuilder::getAllBroken();
            $index = 1;

            foreach ($vhosts as $vhost) {
                $message = ($index++) . '. ' . $vhost->metadata->hostName;
                $this->registry->addResult(Formatter::formatSentence($message, 'Broken'));
            }

            if ($index === 1) {
                $this->registry->addResult('(Empty)');
            }
        }

        /**
         * Parses command arguments.
         *
         * This command does not require arguments, so parse always returns null.
         *
         * @param string ...$args The command arguments (unused).
         *
         * @return string|null Always null.
         */
        public function parse(string ...$args): ?string
        {
            return null;
        }
    }

}
