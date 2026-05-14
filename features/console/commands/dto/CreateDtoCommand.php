<?php

/**
 * Description of CreateDtoCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\dto {

    use features\console\builders\DtoBuilder;
    use features\console\builders\EntityBuilder;
    use features\console\builders\ModuleBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\ModuleName;
    use features\console\helpers\ResourceName;
    use features\console\printer\ConsoleIO;

    final class CreateDtoCommand extends CommandContract
    {

        private readonly ModuleName $moduleName;
        private readonly string $projectName;
        private readonly string $versionNumber;
        private readonly string $dtoName;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'create:dto', 'project_name@version_number@module_name@dto_name', 'Create a Data Transfer Object (DTO)', 'blog@v1@posts@PostCreationDto');
        }

        public function execute(): void
        {
            $module = ModuleBuilder::fromModuleName($this->moduleName, $this->projectName, $this->versionNumber);
            $dto = new DtoBuilder(new EntityBuilder($module), $this->dtoName);
            $dto->build(fn($s) => $this->registry->addResult($s));
        }

        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $this->projectName = ConsoleIO::read('What is the project name?', $this->validIdentifier);
                $this->moduleName = ModuleName::create(ConsoleIO::read('What is the module name?', $this->validIdentifier));
                $this->versionNumber = ConsoleIO::read('What is the project version number?', $this->validIdentifier);
                $this->dtoName = ModuleName::create(ConsoleIO::read('What is the name of the DTO?', $this->validIdentifier))->className;
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 4) {
                    throw new \ArgumentCountError('Atleast four arguments are required.');
                }
                $this->projectName = ResourceName::create($values[0])->shortName;
                $this->versionNumber = ResourceName::create($values[1])->shortName;
                $this->moduleName = ModuleName::create($values[2]);
                $this->dtoName = ModuleName::create($values[3])->className;
            }
            $parameters = $this->projectName . self::SEPARATOR . $this->versionNumber;
            $parameters .= self::SEPARATOR . $this->moduleName->originalValue;
            return $parameters . self::SEPARATOR . $this->dtoName;
        }
    }

}
