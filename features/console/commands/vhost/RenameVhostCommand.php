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
    use features\console\printer\ConsoleIO;

    final class RenameVhostCommand extends CommandContract
    {

        private readonly string $oldName;
        private readonly string $newName;

        public function __construct()
        {
            parent::__construct('rename:vhost', 'old_name new_name', 'Rename a virtual host file from old name to a new name', 'localhost blog.com');
        }

        public function execute(): void
        {
            $vhost = VirtualHostBuilder::fromHostname($this->oldName);
            $vhost->rename($this->newName);
        }

        public function parse(string ...$args): CommandContract
        {
            if (empty($args)) {
                $this->oldName = ConsoleIO::input('What is the name of the host to rename?', $this->validHostName);
                $this->newName = ConsoleIO::input('What is the new name?', $this->validHostName);
            } else if (count($args) < 2) {
                throw new \ArgumentCountError('Atleast two argument is required.');
            } else {
                self::validateHostName($args[0]);
                self::validateHostName($args[1]);
                $this->oldName = $args[0];
                $this->newName = $args[1];
            }
            return $this;
        }
    }

}
