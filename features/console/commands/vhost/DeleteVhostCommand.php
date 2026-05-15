<?php

/**
 * Description of DeleteVhostCommand
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

    final class DeleteVhostCommand extends CommandContract
    {

        private readonly string $hostName;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'delete:vhost', 'hostname', 'Delete existing broken virtual host and it\'s associated configurations', 'localhost');
        }

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

        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $this->hostName = HostName::create(ConsoleIO::read('Virtual host name to delete:'));
            } else {
                $this->hostName = HostName::create(ConsoleIO::read('Write again the host name to delete', fn(string $s) => $s === $args[0]));
            }
            return $this->hostName;
        }
    }

}
