<?php

/**
 * Description of ListBrokenCommand
 * @author goddy
 *
 * Created on: May 15, 2026 at 8:59:40 AM
 */

namespace features\console\commands\broken {

    use features\console\builders\AliasBuilder;
    use features\console\builders\VirtualHostBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\Formatter;

    final class ListBrokenCommand extends CommandContract
    {

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'list:broken', null, 'List broken objects that has not connedted to their dependants', null);
        }

        public function execute(): void
        {
            $this->brokenVhosts();
            $this->registry->addResult(PHP_EOL);
            $this->brokenAliases();
        }

        private function brokenAliases(): void
        {
            $this->registry->addResult(Formatter::placeLeft('ALIASES', true));
            $aliases = AliasBuilder::getAllBroken();
            $index = 1;
            foreach ($aliases as $aliasPath) {
                $message = ($index++) . '. ' . basename($aliasPath, '.alias');
                $this->registry->addResult(Formatter::formatSentence($message, 'Broken'));
            }
            if ($index === 1) {
                $this->registry->addResult('(Empty)');
            }
        }

        private function brokenVhosts(): void
        {
            $this->registry->addResult(Formatter::placeLeft('VIRTUAL HOSTS', true));
            $vhosts = VirtualHostBuilder::getAllBroken();
            $index = 1;
            foreach ($vhosts as $vhost) {
                $message = ($index++) . '. ' . $vhost->metadata->hostName;
                $this->registry->addResult(Formatter::formatSentence($message, 'Broken'));
            }
            if ($index === 1) {
                $this->registry->addResult('(Empty)');
            }
        }

        public function parse(string ...$args): ?string
        {
            return null;
        }
    }

}
