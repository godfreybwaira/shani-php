<?php

/**
 * Description of ListVhostCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\vhost {

    use features\console\builders\VirtualHostBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\Formatter;

    final class ListVhostCommand extends CommandContract
    {

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'list:vhost', null, 'Show the list of all existing virtual hosts', null);
        }

        public function execute(): void
        {
            $hosts = VirtualHostBuilder::getAll();
            $this->registry->addResult(Formatter::formatSentence('#. HOST[ PROJECT ]', 'STATUS', separator: ' '));
            foreach ($hosts as $key => $host) {
                $status = $host->metadata->projectExists() ? 'OK' : 'No Project';
                $message = ($key + 1) . '. ' . $host->metadata->hostName . '[ ' . $host->metadata->projectName . ' ]';
                $this->registry->addResult(Formatter::formatSentence($message, $status));
            }
        }

        public function parse(string ...$args): ?string
        {
            return null;
        }
    }

}
