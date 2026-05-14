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
    use features\console\CommandRegistry;
    use features\console\helpers\HostName;
    use features\console\printer\ConsoleIO;

    final class LocateVhostCommand extends CommandContract
    {

        private readonly string $hostName;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'locate:vhost', 'hostname', 'Show the full path to an existing virtual host', 'blog');
        }

        public function execute(): void
        {
            $vhost = VirtualHostBuilder::fromHostName($this->hostName);
            $vhost->locate();
        }

        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $this->hostName = ConsoleIO::read('Virtual host name to locate:', $this->validHostName);
            } else if (count($args) < 1) {
                throw new \ArgumentCountError('Atleast one argument is allowed.');
            } else {
                $this->hostName = HostName::create($args[0]);
            }
            return $this->hostName;
        }
    }

}
