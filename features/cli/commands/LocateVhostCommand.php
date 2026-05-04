<?php

/**
 * Description of LocateVhostCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\cli\commands {

    use features\cli\builders\VirtualHostBuilder;
    use features\cli\CommandContract;

    final class LocateVhostCommand extends CommandContract
    {

        private readonly string $hostname;

        public function __construct()
        {
            parent::__construct('locate:vhost', 'hostname', 'Show the full path to an existing virtual host.', 'blog');
        }

        public function execute(): void
        {
            $vhost = new VirtualHostBuilder($this->hostname);
            $vhost->locate();
        }

        public function parse(string ...$args): CommandContract
        {
            if (count($args) > 1) {
                throw new \ArgumentCountError('Only one argument is required.');
            }
            $this->validateIdentifier($args[0]);
            $this->hostname = $args[0];
            return $this;
        }
    }

}
