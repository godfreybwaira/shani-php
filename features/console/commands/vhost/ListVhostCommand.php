<?php

/**
 * Command to list all existing virtual hosts.
 *
 * This command retrieves all configured virtual hosts and displays them
 * along with their associated project and status. It can be used to quickly
 * verify which hosts are available and whether their projects exist.
 *
 * @author goddy
 * @created May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\vhost {

    use features\console\builders\VirtualHostBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\Formatter;

    /**
     * Command to list all existing virtual hosts.
     *
     * This command retrieves all configured virtual hosts and displays them
     * along with their associated project and status. It can be used to quickly
     * verify which hosts are available and whether their projects exist.
     *
     * @author goddy
     * @created May 3, 2026 at 8:59:28 PM
     */
    final class ListVhostCommand extends CommandContract
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
                    'list:vhost',
                    null,
                    'Show the list of all existing virtual hosts',
                    null
            );
        }

        /**
         * Executes the list operation.
         *
         * Retrieves all virtual hosts using {@see VirtualHostBuilder::getAll()},
         * formats them with their status, and adds the results to the registry.
         *
         * Status meanings:
         * - "OK" → The associated project exists.
         * - "No Project" → The project does not exist.
         *
         * @return void
         */
        public function execute(): void
        {
            $hosts = VirtualHostBuilder::getAll();
            $this->registry->addResult(
                    Formatter::formatSentence('#. HOST[ PROJECT ]', 'STATUS', separator: ' ')
            );

            foreach ($hosts as $key => $host) {
                $status = $host->metadata->projectExists() ? 'OK' : 'No Project';
                $message = ($key + 1) . '. ' . $host->metadata->hostName . '[ ' . $host->metadata->projectName . ' ]';
                $this->registry->addResult(Formatter::formatSentence($message, $status));
            }
        }

        /**
         * Parses command arguments.
         *
         * This command does not require arguments, so parsing always returns null.
         *
         * @param string ...$args Ignored arguments.
         *
         * @return string|null Always null.
         */
        public function parse(string ...$args): ?string
        {
            return null;
        }
    }

}
