<?php

namespace features\console\commands\vhost {

    use features\console\builders\VirtualHostBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\HostName;
    use features\console\ResourceSelector;

    /**
     * Command to locate a virtual host file.
     *
     * This command shows the full filesystem path to an existing virtual host
     * configuration. It can be executed either interactively (via console prompts)
     * or by passing the hostname directly as an argument.
     *
     * @author goddy
     * @created May 3, 2026 at 8:59:28 PM
     */
    final class LocateVhostCommand extends CommandContract
    {

        /**
         * The hostname of the virtual host to locate.
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
                    'locate:vhost',
                    'hostname',
                    'Show the full path to an existing virtual host',
                    'blog'
            );
        }

        /**
         * Executes the locate operation.
         *
         * Uses the {@see VirtualHostBuilder} to find the virtual host
         * by its hostname and display its full path.
         *
         * @return void
         */
        public function execute(): void
        {
            $vhost = VirtualHostBuilder::fromHostName($this->hostName);
            $vhost->locate();
        }

        /**
         * Parses command arguments or prompts the user interactively.
         *
         * - If no arguments are provided, prompts the user to select a host.
         * - If fewer than one argument is provided, throws an error.
         * - Otherwise, uses the provided argument directly.
         *
         * @param string ...$args The command arguments (hostname).
         *
         * @return string|null The selected or provided hostname.
         *
         * @throws \ArgumentCountError If fewer than one argument is provided.
         */
        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $selector = new ResourceSelector();
                $this->hostName = $selector->selectHost();
            } else if (count($args) < 1) {
                throw new \ArgumentCountError('At least one argument is required.');
            } else {
                $this->hostName = HostName::create($args[0]);
            }
            return $this->hostName;
        }
    }

}
