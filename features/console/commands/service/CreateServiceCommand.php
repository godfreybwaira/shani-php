<?php

/**
 * Description of CreateServiceCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\service {

    use features\console\builders\ModuleBuilder;
    use features\console\builders\ServiceBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\ModuleName;
    use features\console\helpers\ResourceName;
    use features\console\printer\ConsoleIO;

    final class CreateServiceCommand extends CommandContract
    {

        private readonly string $projectName;
        private readonly string $versionNumber;
        private readonly ModuleName $moduleName;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'create:service', 'project_name@version_number@module_name', 'Create new module service class', 'blog@v1@posts');
        }

        public function execute(): void
        {
            $module = ModuleBuilder::fromModuleName($this->moduleName, $this->projectName, $this->versionNumber);
            $service = new ServiceBuilder($module);
            $service->build(fn($s) => $this->registry->addResult($s));
        }

        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $this->projectName = ConsoleIO::read('What is the project name?', $this->validIdentifier);
                $this->versionNumber = ConsoleIO::read('What is the project version number?', $this->validIdentifier);
                $this->moduleName = ModuleName::create(ConsoleIO::read('What is the module name?', $this->validIdentifier));
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 3) {
                    throw new \ArgumentCountError('Atleast three arguments are required.');
                }
                $this->projectName = ResourceName::create($values[0])->shortName;
                $this->versionNumber = ResourceName::create($values[1])->shortName;
                $this->moduleName = ModuleName::create($values[2]);
            }
            $parameters = $this->projectName . self::SEPARATOR . $this->versionNumber;
            return $parameters . self::SEPARATOR . $this->moduleName->originalValue;
        }
    }

}
