<?php

/**
 * Command to delete a virtual host alias.
 *
 * This command deletes a specific alias from the project. It can be executed
 * interactively (via console prompts) or by passing arguments directly in the
 * format "alias_name".
 *
 * If the alias does not exist, an error will be thrown.
 *
 * @author goddy
 * @created May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\alias {

    use features\console\builders\AliasBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\HostName;
    use features\console\ResourceSelector;

    final class DeleteAliasCommand extends CommandContract
    {

        /**
         * The alias name to delete.
         *
         * @var string
         */
        private readonly string $aliasName;

        /**
         * Initializes the command with its registry and metadata.
         *
         * @param CommandRegistry $registry The command registry instance.
         */
        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'delete:alias', 'alias_name', 'Delete an alias', 'blog.com');
        }

        /**
         * Executes the alias deletion operation.
         *
         * Uses the {@see AliasBuilder} to delete the alias. The result is logged
         * in the registry.
         *
         * @return void
         */
        public function execute(): void
        {
            $alias = AliasBuilder::fromAliasName($this->aliasName);
            $alias->delete(fn($s) => $this->registry->addResult($s));
        }

        /**
         * Parses command arguments or prompts the user interactively.
         *
         * - If no arguments are provided, prompts the user to select an alias interactively.
         * - If arguments are provided, expects the format "alias_name".
         *
         * @param string ...$args The command arguments (alias_name).
         *
         * @return string|null The alias name or null if skipped.
         *
         * @throws \ArgumentCountError If fewer than one argument is provided.
         */
        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $this->aliasName = (new ResourceSelector())->selectAlias();
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 1) {
                    throw new \ArgumentCountError('At least one argument is required.');
                }
                $this->aliasName = HostName::create($values[0]);
            }
            return $this->aliasName;
        }
    }

}
