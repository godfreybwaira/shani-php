<?php

/**
 * Description of ListHostAliasCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\alias {

    use features\console\builders\AliasBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\Formatter;
    use features\console\helpers\HostName;
    use features\console\ResourceSelector;

    final class ListHostAliasCommand extends CommandContract
    {

        private readonly string $hostName;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'list:alias', 'hostname', 'Show all available host aliases', 'localhost');
        }

        public function execute(): void
        {
            $aliases = AliasBuilder::getAllByHostName($this->hostName);
            if (!$aliases->valid()) {
                throw new \InvalidArgumentException('No alias found for host "' . $this->hostName . '"');
            }
            $this->registry->addResult(Formatter::formatSentence('#. ALIAS', 'HOST', separator: ' '));
            foreach ($aliases as $idx => $alias) {
                $message = ($idx + 1) . '. ' . $alias->aliasName;
                $this->registry->addResult(Formatter::formatSentence($message, $alias->vhost->metadata->hostName));
            }
        }

        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $this->hostName = (new ResourceSelector())->selectHost();
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 1) {
                    throw new \ArgumentCountError('Atleast one argument is required.');
                }
                $this->hostName = HostName::create($values[0]);
            }
            return $this->hostName;
        }
    }

}
