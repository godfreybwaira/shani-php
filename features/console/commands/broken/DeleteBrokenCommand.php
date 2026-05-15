<?php

/**
 * Description of DeleteBrokenCommand
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
    use features\console\helpers\ResourceName;
    use features\console\printer\ConsoleIO;

    final class DeleteBrokenCommand extends CommandContract
    {

        private const BROKEN_OBJECTS = ['vhost', 'alias'];

        private readonly string $object;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'delete:broken', implode('|', self::BROKEN_OBJECTS), 'Delete broken objects that has not connedted to their dependants', 'alias');
        }

        public function execute(): void
        {
            match ($this->object) {
                'alias' => $this->deleteAliases(),
                'vhost' => $this->deleteVhosts(),
            };
        }

        private function deleteVhosts(): void
        {
            $vhosts = VirtualHostBuilder::getAllBroken();
            foreach ($vhosts as $vhost) {
                $vhost->delete(fn($s) => $this->registry->addResult($s));
            }
        }

        private function deleteAliases(): void
        {
            $aliases = AliasBuilder::getAllBroken();
            foreach ($aliases as $aliasPath) {
                $result = unlink($aliasPath) ? 'Success' : 'failed';
                $this->registry->addResult(Formatter::formatSentence('Deleting alias "' . basename($aliasPath, '.alias') . '"', $result));
            }
        }

        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $this->object = ConsoleIO::read('What are the broken objects to delete?', fn(string $s) => true);
            } else {
                $this->object = ResourceName::create($args[0])->shortName;
            }
            if (!in_array($this->object, self::BROKEN_OBJECTS)) {
                throw new \InvalidArgumentException('Please use these as values: ' . implode(', ', self::BROKEN_OBJECTS));
            }
            return $this->object;
        }
    }

}
