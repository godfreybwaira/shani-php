<?php

/**
 * Description of RenameVhostCommand
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

    final class RenameVhostCommand extends CommandContract
    {

        private readonly string $oldName;
        private readonly string $newName;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'rename:vhost', 'old_name new_name', 'Rename a virtual host file from old name to a new name', 'localhost blog.com');
        }

        public function execute(): void
        {
            $vhost = VirtualHostBuilder::fromHostname($this->oldName);
            $vhost->rename($this->newName, fn($s) => $this->registry->addResult($s));
        }

        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $selector = new ResourceSelector();
                $this->oldName = $selector->selectHost();
                $this->newName = ConsoleIO::read('What is the new name?', $this->validHostName);
            } else if (count($args) < 2) {
                throw new \ArgumentCountError('Atleast two arguments are required.');
            } else {
                $this->oldName = HostName::create($args[0]);
                $this->newName = HostName::create($args[1]);
            }
            return $this->oldName . ' ' . $this->newName;
        }
    }

}
