<?php

/**
 * Command to delete a virtual host.
 *
 * This command removes a broken virtual host and its associated configurations.
 * It ensures that the host is not linked to an existing project before deletion.
 * Can be executed interactively (via console prompts) or by passing arguments directly.
 *
 * @author goddy
 * @created May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\vhost {

    use features\console\builders\VirtualHostBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\HostName;
    use features\console\printer\ConsoleIO;
    use features\console\ResourceSelector;

    final class DeleteVhostCommand extends CommandContract
    {

        /**
         * The hostname of the virtual host to delete.
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
                    'delete:vhost',
                    'hostname',
                    'Delete existing broken virtual host and its associated configurations',
                    'localhost'
            );
        }

        /**
         * Executes the delete operation.
         *
         * - If the host is linked to an existing project, deletion is blocked
         *   and a {@see \RuntimeException} is thrown.
         * - Otherwise, deletes the host and logs the result in the registry.
         *
         * @return void
         *
         * @throws \RuntimeException If the host is connected to a project.
         */
        public function execute(): void
        {
            $vhost = VirtualHostBuilder::fromHostName($this->hostName);

            if ($vhost->metadata->projectExists()) {
                $message = 'Host "' . $this->hostName . '" is connected to a project "';
                $message .= $vhost->metadata->projectName . '", so cannot be deleted.';
                throw new \RuntimeException($message);
            }

            $vhost->delete(fn($s) => $this->registry->addResult($s));
        }

        /**
         * Parses command arguments or prompts the user interactively.
         *
         * - If no arguments are provided, prompts the user to select a host.
         * - If arguments are provided, asks the user to confirm the host name
         *   before deletion.
         *
         * @param string ...$args The command arguments (hostname).
         *
         * @return string|null The confirmed hostname to delete.
         */
        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $selector = new ResourceSelector();
                $this->hostName = $selector->selectHost();
            } else {
                $name = ConsoleIO::read('Write again the host name to delete', fn(string $s) => $s === $args[0]);
                $this->hostName = HostName::create($name);
            }

            return $this->hostName;
        }
    }

}
