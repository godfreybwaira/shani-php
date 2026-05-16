<?php

/**
 * Command to delete broken objects in the project.
 *
 * This command removes broken virtual hosts and aliases that are not properly
 * connected to their dependents. It helps clean up misconfigured or unused
 * resources to maintain project integrity.
 *
 * If no broken objects are found, nothing will be deleted.
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
    use features\console\helpers\ResourceName;
    use features\console\printer\ConsoleIO;

    final class DeleteBrokenCommand extends CommandContract
    {

        /**
         * Supported broken object types.
         *
         * @var array<string>
         */
        private const BROKEN_OBJECTS = ['vhost', 'alias'];

        /**
         * The type of broken object to delete (either "vhost" or "alias").
         *
         * @var string
         */
        private readonly string $object;

        /**
         * Initializes the command with its registry and metadata.
         *
         * @param CommandRegistry $registry The command registry instance.
         */
        public function __construct(CommandRegistry $registry)
        {
            parent::__construct(
                    $registry,
                    'delete:broken',
                    implode('|', self::BROKEN_OBJECTS),
                    'Delete broken objects that has not connected to their dependents',
                    'alias'
            );
        }

        /**
         * Executes the deletion of broken objects.
         *
         * Depending on the selected object type, deletes either broken aliases
         * or broken virtual hosts.
         *
         * @return void
         */
        public function execute(): void
        {
            match ($this->object) {
                'alias' => $this->deleteAliases(),
                'vhost' => $this->deleteVhosts(),
            };
        }

        /**
         * Deletes broken virtual hosts.
         *
         * Retrieves all broken virtual hosts using {@see VirtualHostBuilder::getAllBroken()}
         * and deletes them. Each deletion result is logged in the registry.
         *
         * @return void
         */
        private function deleteVhosts(): void
        {
            $vhosts = VirtualHostBuilder::getAllBroken();
            foreach ($vhosts as $vhost) {
                $vhost->delete(fn($s) => $this->registry->addResult($s));
            }
        }

        /**
         * Deletes broken aliases.
         *
         * Retrieves all broken aliases using {@see AliasBuilder::getAllBroken()}
         * and deletes them using `unlink()`. Each deletion result is logged in the registry.
         *
         * @return void
         */
        private function deleteAliases(): void
        {
            $aliases = AliasBuilder::getAllBroken();
            foreach ($aliases as $aliasPath) {
                $result = unlink($aliasPath) ? 'Success' : 'failed';
                $message = Formatter::formatSentence('Deleting alias "' . basename($aliasPath, '.alias') . '"', $result);
                $this->registry->addResult($message);
            }
        }

        /**
         * Parses command arguments or prompts the user interactively.
         *
         * - If no arguments are provided, prompts the user to specify the broken object type.
         * - If arguments are provided, expects either "alias" or "vhost".
         *
         * @param string ...$args The command arguments (object type).
         *
         * @return string|null The selected object type ("alias" or "vhost").
         *
         * @throws \InvalidArgumentException If an unsupported object type is provided.
         */
        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $this->object = ConsoleIO::read('What are the broken objects to delete?', fn(string $s) => true);
            } else {
                $this->object = ResourceName::create($args[0])->shortName;
            }

            if (!in_array($this->object, self::BROKEN_OBJECTS)) {
                throw new \InvalidArgumentException('Please use these as values: ' . implode(', ', self::BROKEN_OBJECTS));
            }

            return $this->object;
        }
    }

}
