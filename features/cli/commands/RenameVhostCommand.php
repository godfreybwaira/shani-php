<?php

/**
 * Description of RenameVhostCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\cli\commands {

    use features\cli\builders\VirtualHostBuilder;
    use features\cli\CommandContract;

    final class RenameVhostCommand extends CommandContract
    {

        private readonly string $oldName;
        private readonly string $newName;

        public function __construct()
        {
            parent::__construct('rename:vhost', 'old_name new_name', 'Rename a virtual host file from old name to a new name.', 'localhost blog.com');
        }

        public function execute(): void
        {
            $vhost = new VirtualHostBuilder($this->oldName);
            $vhost->rename($this->newName);
        }

        public function parse(string ...$args): CommandContract
        {
            if (count($args) < 2) {
                throw new \ArgumentCountError('Atleast two argument is required.');
            }
            $this->validateHostName($args[0]);
            $this->validateHostName($args[1]);
            $this->oldName = $args[0];
            $this->newName = $args[1];
            return $this;
        }
    }

}
