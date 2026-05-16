<?php

/**
 * Command to start the PHP development server.
 *
 * This command launches the built-in PHP development server on a specified port.
 * If no port number is provided, it defaults to port 8000.
 *
 * @author goddy
 * @created May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\misc {

    use features\console\CommandContract;
    use features\console\CommandRegistry;

    final class StartServerCommand extends CommandContract
    {

        /**
         * The port number on which the server will run.
         *
         * @var int
         */
        private readonly int $portNumber;

        /**
         * Initializes the command with its registry and metadata.
         *
         * @param CommandRegistry $registry The command registry instance.
         */
        public function __construct(CommandRegistry $registry)
        {
            parent::__construct(
                    $registry,
                    'serve',
                    '[port_number]',
                    'Start application development server on a given port number or default port',
                    '8000'
            );
        }

        /**
         * Executes the server start operation.
         *
         * Runs the built-in PHP server using the specified port number.
         *
         * @return void
         */
        public function execute(): void
        {
            system('php -S localhost:' . $this->portNumber);
        }

        /**
         * Parses command arguments or defaults to port 8000.
         *
         * - If a port number is provided, uses that port.
         * - If no arguments are provided, defaults to port 8000.
         *
         * @param string ...$args The command arguments (optional port number).
         *
         * @return string|null The port number as a string.
         */
        public function parse(string ...$args): ?string
        {
            $this->portNumber = !empty($args) ? (int) $args[0] : 8000;
            return (string) $this->portNumber;
        }
    }

}
