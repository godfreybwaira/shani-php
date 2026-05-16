<?php

/**
 * Description of CreateControllerCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\controller {

    use features\console\builders\ControllerBuilder;
    use features\console\builders\ModuleBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\ModuleName;
    use features\console\helpers\ResourceName;
    use features\console\printer\ConsoleIO;

    final class CreateControllerCommand extends CommandContract
    {

        private readonly string $projectName;
        private readonly string $versionNumber;
        private readonly ModuleName $moduleName;
        private readonly string $requestMethod;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'create:controller', 'project_name@version_number@module_name@request_method', 'Create a new project controller, its associated service, dto, entity, view and language file', 'blog@v1@posts@get');
        }

        public function execute(): void
        {
            $module = ModuleBuilder::fromModuleName($this->moduleName, $this->projectName, $this->versionNumber);
            $controller = new ControllerBuilder($module, $this->requestMethod);
            $controller->build(fn($s) => $this->registry->addResult($s));
        }

        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $this->projectName = ConsoleIO::read('What is the project name?', $this->validIdentifier);
                $this->versionNumber = ConsoleIO::read('What is the project version number?', $this->validIdentifier);
                $this->moduleName = ModuleName::create(ConsoleIO::read('What is the module name?', $this->validIdentifier));
                $this->requestMethod = ConsoleIO::read('What is the request method?', $this->validIdentifier);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 4) {
                    throw new \ArgumentCountError('Atleast four arguments are required.');
                }
                $this->projectName = ResourceName::create($values[0])->shortName;
                $this->versionNumber = ResourceName::create($values[1])->shortName;
                $this->moduleName = ModuleName::create($values[2]);
                $this->requestMethod = ResourceName::create($values[3])->shortName;
            }
            $parameters = $this->projectName . self::SEPARATOR . $this->versionNumber;
            $parameters .= self::SEPARATOR . $this->moduleName->className . self::SEPARATOR;
            return $parameters . $this->requestMethod;
        }
    }

}
