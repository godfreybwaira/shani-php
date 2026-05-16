<?php

namespace features\console\commands\vhost {

    use features\console\builders\VirtualHostBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\HostName;
    use features\console\printer\ConsoleIO;

    /**
     * Command to rename a virtual host file.
     *
     * This command allows renaming an existing virtual host configuration
     * from an old hostname to a new hostname. It can be executed either
     * interactively (via console prompts) or by passing arguments directly.
     *
     * @author goddy
     * @created May 3, 2026 at 8:59:28 PM
     */
    final class RenameVhostCommand extends CommandContract
    {

        /**
         * The old hostname to be renamed.
         *
         * @var string
         */
        private readonly string $oldName;

        /**
         * The new hostname to rename to.
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
                    'rename:vhost',
                    'old_name new_name',
                    'Rename a virtual host file from old name to a new name',
                    'localhost blog.com'
            );
        }

        /**
         * Executes the rename operation.
         *
         * Uses the {@see VirtualHostBuilder} to locate the virtual host
         * by its old name and rename it to the new name.
         *
         * @return void
         */
        public function execute(): void
        {
            $vhost = VirtualHostBuilder::fromHostname($this->oldName);
            $vhost->rename($this->newName, fn($s) => $this->registry->addResult($s));
        }

        /**
         * Parses command arguments or prompts the user interactively.
         *
         * - If no arguments are provided, prompts the user to select the old host
         *   and enter the new name.
         * - If fewer than two arguments are provided, throws an error.
         * - Otherwise, uses the provided arguments directly.
         *
         * @param string ...$args The command arguments (old name, new name).
         *
         * @return string|null A string containing "oldName newName" or null if skipped.
         *
         * @throws \ArgumentCountError If fewer than two arguments are provided.
         */
        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $selector = new ResourceSelector();
                $this->oldName = $selector->selectHost();
                $this->newName = ConsoleIO::read('What is the new name?', $this->validHostName);
            } else if (count($args) < 2) {
                throw new \ArgumentCountError('At least two arguments are required.');
            } else {
                $this->oldName = HostName::create($args[0]);
                $this->newName = HostName::create($args[1]);
            }
            return $this->oldName . ' ' . $this->newName;
        }
    }

}
