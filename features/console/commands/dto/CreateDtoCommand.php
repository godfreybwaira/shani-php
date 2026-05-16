<?php

/**
 * Command to create a new Data Transfer Object (DTO).
 *
 * This command generates a DTO inside a specified module of a project version.
 * It can be executed interactively (via console prompts) or by passing arguments
 * directly in the format "project@version@module@dto".
 *
 * @author goddy
 * @created May 3, 2026 at 8:59:28 PM
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
    use features\console\ResourceSelector;

    final class CreateDtoCommand extends CommandContract
    {

        /**
         * The module name within the project version.
         *
         * @var ModuleName
         */
        private readonly ModuleName $moduleName;

        /**
         * The name of the project containing the module.
         *
         * @var string
         */
        private readonly string $projectName;

        /**
         * The version number of the project.
         *
         * @var string
         */
        private readonly string $versionNumber;

        /**
         * The name of the DTO to be created.
         *
         * @var string
         */
        private readonly string $dtoName;

        /**
         * Initializes the command with its registry and metadata.
         *
         * @param CommandRegistry $registry The command registry instance.
         */
        public function __construct(CommandRegistry $registry)
        {
            parent::__construct(
                    $registry,
                    'create:dto',
                    'project_name@version_number@module_name@dto_name',
                    'Create a Data Transfer Object (DTO)',
                    'blog@v1@posts@PostCreationDto'
            );
        }

        /**
         * Executes the DTO creation operation.
         *
         * Uses the {@see ModuleBuilder} to build the module and
         * {@see DtoBuilder} (via {@see EntityBuilder}) to generate the new DTO.
         * The result is logged in the registry.
         *
         * @return void
         */
        public function execute(): void
        {
            $module = ModuleBuilder::fromModuleName($this->moduleName, $this->projectName, $this->versionNumber);
            $dto = new DtoBuilder(new EntityBuilder($module), $this->dtoName);
            $dto->build(fn($s) => $this->registry->addResult($s));
        }

        /**
         * Parses command arguments or prompts the user interactively.
         *
         * - If no arguments are provided, prompts the user to select a project,
         *   version, and module interactively, then asks for the DTO name.
         * - If arguments are provided, expects the format "project@version@module@dto".
         *   Splits the string into project name, version number, module name, and DTO name.
         *
         * @param string ...$args The command arguments (project@version@module@dto).
         *
         * @return string|null A string containing "project@version@module@dto" or null if skipped.
         *
         * @throws \ArgumentCountError If fewer than four arguments are provided.
         */
        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $selector = new ResourceSelector();
                $this->projectName = $selector->selectProject();
                $this->versionNumber = $selector->selectProjectVersion();
                $this->moduleName = $selector->selectModule();
                $this->dtoName = ModuleName::create(ConsoleIO::read('Enter the DTO name:', $this->validIdentifier))->className;
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 4) {
                    throw new \ArgumentCountError('At least four arguments are required.');
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
