<?php

/**
 * Command to locate a virtual host alias.
 *
 * This command shows the filesystem path to a specific alias within the project.
 * It can be executed interactively (via console prompts) or by passing arguments
 * directly in the format "alias".
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

    final class LocateAliasCommand extends CommandContract
    {

        /**
         * The alias name to locate.
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
            parent::__construct(
                    $registry,
                    'locate:alias',
                    'alias',
                    'Show the full path to an existing virtual host alias',
                    'blog.com'
            );
        }

        /**
         * Executes the alias locate operation.
         *
         * Uses the {@see AliasBuilder} to locate the alias and display its path.
         *
         * @return void
         */
        public function execute(): void
        {
            $alias = AliasBuilder::fromAliasName($this->aliasName);
            $alias->locate();
        }

        /**
         * Parses command arguments or prompts the user interactively.
         *
         * - If no arguments are provided, prompts the user to select an alias interactively.
         * - If arguments are provided, expects the format "alias".
         *
         * @param string ...$args The command arguments (alias).
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
                if (count($args) < 1) {
                    throw new \ArgumentCountError('At least one argument is required.');
                }
                $this->aliasName = HostName::create($args[0]);
            }
            return $this->aliasName;
        }
    }

}
