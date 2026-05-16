<?php

/**
 * Command to rename an alias.
 *
 * This command renames an existing alias from its old name to a new name.
 * It can be executed interactively (via console prompts) or by passing
 * arguments directly in the format "old_name new_name".
 *
 * If the alias does not exist or the new name is invalid, an error will be thrown.
 *
 * @author goddy
 * @created May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\alias {

    use features\console\builders\AliasBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\HostName;
    use features\console\printer\ConsoleIO;
    use features\console\ResourceSelector;

    final class RenameAliasCommand extends CommandContract
    {

        /**
         * The old alias name to be renamed.
         *
         * @var string
         */
        private readonly string $oldName;

        /**
         * The new alias name to replace the old one.
         *
         * @var string
         */
        private readonly string $newName;

        /**
         * Initializes the command with its registry and metadata.
         *
         * @param CommandRegistry $registry The command registry instance.
         */
        public function __construct(CommandRegistry $registry)
        {
            parent::__construct(
                    $registry,
                    'rename:alias',
                    'old_name new_name',
                    'Rename an alias from old name to a new name',
                    'blog.com blog.co.tz'
            );
        }

        /**
         * Executes the alias rename operation.
         *
         * Uses the {@see AliasBuilder} to rename the alias from old name to new name.
         * The result is logged in the registry.
         *
         * @return void
         */
        public function execute(): void
        {
            $alias = AliasBuilder::fromAliasName($this->oldName);
            $alias->rename($this->newName, fn($s) => $this->registry->addResult($s));
        }

        /**
         * Parses command arguments or prompts the user interactively.
         *
         * - If no arguments are provided, prompts the user to select an alias
         *   and then asks for the new alias name.
         * - If arguments are provided, expects the format "old_name new_name".
         *   Splits the string into old alias name and new alias name.
         *
         * @param string ...$args The command arguments (old_name new_name).
         *
         * @return string|null A string containing "old_name new_name" or null if skipped.
         *
         * @throws \ArgumentCountError If fewer than two arguments are provided.
         */
        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $this->oldName = (new ResourceSelector())->selectAlias();
                $this->newName = ConsoleIO::read('What is the new alias name?', $this->validHostName);
            } else {
                if (count($args) < 2) {
                    throw new \ArgumentCountError('At least two arguments are required.');
                }
                $this->oldName = HostName::create($args[0]);
                $this->newName = HostName::create($args[1]);
            }
            return $this->oldName . ' ' . $this->newName;
        }
    }

}
