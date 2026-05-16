<?php

/**
 * Description of StartServerCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\misc {

    use features\console\CommandContract;
    use features\console\CommandRegistry;

    final class StartServerCommand extends CommandContract
    {

        private readonly int $portNumber;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'serve', '[port_number]', 'Start application development server on a given port number or default port', '8000');
        }

        public function execute(): void
        {
            system('php -S localhost:' . $this->portNumber);
        }

        public function parse(string ...$args): ?string
        {
            $this->portNumber = !empty($args) ? (int) $args[0] : 8000;
            return $this->portNumber;
        }
    }

}
