<?php

/**
 * Description of LocateVhostCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\vhost {

    use features\console\builders\VirtualHostBuilder;
    use features\console\CommandContract;
    use features\console\printer\ConsoleIO;

    final class LocateVhostCommand extends CommandContract
    {

        private readonly string $hostname;

        public function __construct()
        {
            parent::__construct('locate:vhost', 'hostname', 'Show the full path to an existing virtual host', 'blog');
        }

        public function execute(): void
        {
            $vhost = VirtualHostBuilder::fromHostname($this->hostname);
            $vhost?->locate();
        }

        public function parse(string ...$args): CommandContract
        {
            if (empty($args)) {
                $this->hostname = ConsoleIO::input('Virtual host name to locate:', $this->validHostName);
            } else if (count($args) < 1) {
                throw new \ArgumentCountError('Atleast one argument is allowed.');
            } else {
                $this->validateIdentifier($args[0]);
                $this->hostname = $args[0];
            }
            return $this;
        }
    }

}
