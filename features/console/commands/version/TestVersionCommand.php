<?php

/**
 * Description of TestVersionCommand
 * @author goddy
 *
 * Created on: May 15, 2026 at 3:24:05 PM
 */

namespace features\console\commands\version {

    use features\console\builders\ProjectVersionBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\ModuleName;
    use features\console\helpers\ResourceName;
    use features\console\printer\PrintedText;
    use features\console\ResourceSelector;

    final class TestVersionCommand extends CommandContract
    {

        private readonly string $projectName;
        private readonly string $versionNumber;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'test:version', 'project_name@version_number', 'Run application version test', 'blog@v1');
        }

        public function execute(): void
        {
            $version = ProjectVersionBuilder::fromProjectName($this->projectName, $this->versionNumber);
            if (!$version->runTest()) {
                throw new \Exception('Test Failed');
            }
            $this->registry->addResult(PrintedText::success('Test Passed'));
        }

        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $selector = new ResourceSelector();
                $this->projectName = $selector->selectProject();
                $this->versionNumber = $selector->selectProjectVersion();
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 2) {
                    throw new \ArgumentCountError('Atleast two arguments are required.');
                }
                $this->projectName = ResourceName::create($values[0])->shortName;
                $this->versionNumber = ModuleName::create($values[1])->directoryName;
            }
            return $this->projectName . self::SEPARATOR . $this->versionNumber;
        }
    }

}
