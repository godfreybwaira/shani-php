<?php

/**
 * Description of CreateAliasCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\alias {

    use features\console\builders\AliasBuilder;
    use features\console\builders\VirtualHostBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\HostName;
    use features\console\printer\ConsoleIO;
    use features\console\ResourceSelector;

    final class CreateAliasCommand extends CommandContract
    {

        private readonly string $aliasName;
        private readonly string $hostName;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'create:alias', 'alias@hostname', 'Create a host alias', 'blog.com@localhost');
        }

        public function execute(): void
        {
            $vhost = VirtualHostBuilder::fromHostName($this->hostName);
            $alias = new AliasBuilder($vhost, $this->aliasName);
            $alias->build(fn($s) => $this->registry->addResult($s));
        }

        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $this->aliasName = ConsoleIO::read('What is the alias name?', $this->validHostName);
                $this->hostName = (new ResourceSelector())->selectHost();
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 2) {
                    throw new \ArgumentCountError('Atleast two arguments are required.');
                }
                $this->aliasName = HostName::create($values[0]);
                $this->hostName = HostName::create($values[1]);
            }
            return $this->aliasName . self::SEPARATOR . $this->hostName;
        }
    }

}
