<?php

/**
 * Description of FrameworkVersionCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\misc {

    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use shani\launcher\Framework;

    final class FrameworkVersionCommand extends CommandContract
    {

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'framework:version', null, 'Show this framework version', null);
        }

        public function execute(): void
        {
            $this->registry->addResult('NAME: ' . Framework::NAME . ' v' . Framework::VERSION);
            $this->registry->addResult('SLOGAN: ' . Framework::SLOGAN);
            $this->registry->addResult('DESCRIPTION: ' . Framework::DESCRIPTION);
            $this->registry->addResult('DEVELOPER: ' . Framework::DEVELOPER);
        }

        public function parse(string ...$args): ?string
        {
            return null;
        }
    }

}
