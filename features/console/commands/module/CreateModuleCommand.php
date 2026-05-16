<?php

/**
 * Description of CreateModuleCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\module {

    use features\console\builders\ModuleBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\ModuleName;
    use features\console\helpers\ResourceName;
    use features\console\ResourceSelector;

    final class CreateModuleCommand extends CommandContract
    {

        private readonly string $projectName;
        private readonly string $versionNumber;
        private readonly ModuleName $moduleName;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'create:module', 'project_name@version_number@module_name', 'Create a new project module', 'blog@v1@posts');
        }

        public function execute(): void
        {
            $module = ModuleBuilder::fromModuleName($this->moduleName, $this->projectName, $this->versionNumber);
            $module->build(fn($s) => $this->registry->addResult($s));
        }

        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $selector = new ResourceSelector();
                $this->projectName = $selector->selectProject();
                $this->versionNumber = $selector->selectProjectVersion();
                $this->moduleName = $selector->selectModule();
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 3) {
                    throw new \ArgumentCountError('Atleast three arguments are required.');
                }
                $this->projectName = ResourceName::create($values[0])->shortName;
                $this->versionNumber = ResourceName::create($values[1])->shortName;
                $this->moduleName = ModuleName::create($values[2]);
            }
            return $this->projectName . self::SEPARATOR . $this->versionNumber . self::SEPARATOR . $this->moduleName->className;
        }
    }

}
