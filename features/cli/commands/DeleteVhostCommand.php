<?php

/**
 * Description of DeleteVhostCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\cli\commands {

    use features\cli\builders\VirtualHostBuilder;
    use features\cli\CommandContract;

    final class DeleteVhostCommand extends CommandContract
    {

        private readonly string $hostname;

        public function __construct()
        {
            parent::__construct('delete:vhost', 'hostname', 'Delete a virtual host file, it\'s aliases and corresponding configuration.', 'localhost');
        }

        public function execute(): void
        {
            $vhost = new VirtualHostBuilder($this->hostname);
            $vhost->delete();
        }

        public function parse(string ...$args): CommandContract
        {
            $values = explode(self::SEPARATOR, $args[0]);
            if (count($values) < 1) {
                throw new \ArgumentCountError('Atleast one argument is required.');
            }
            $this->validateHostName($values[0]);
            $this->hostname = $values[0];
            return $this;
        }
    }

}
