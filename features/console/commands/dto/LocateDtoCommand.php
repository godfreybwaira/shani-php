<?php

/**
 * Command to locate project DTOs.
 *
 * This command shows the filesystem path to the Data Transfer Objects (DTOs)
 * of a specific module within a given project version. It can be executed
 * interactively (via console prompts) or by passing arguments directly
 * in the format "project@version@module".
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
    use features\console\ResourceSelector;

    final class LocateDtoCommand extends CommandContract
    {

        /**
         * The module name within the project version.
         *
         * @var ModuleName
         */
        private readonly ModuleName $moduleName;

        /**
         * The version number of the project.
         *
         * @var string
         */
        private readonly string $versionNumber;

        /**
         * The name of the project containing the module.
         *
         * @var string
         */
        private readonly string $projectName;

        /**
         * Initializes the command with its registry and metadata.
         *
         * @param CommandRegistry $registry The command registry instance.
         */
        public function __construct(CommandRegistry $registry)
        {
            parent::__construct(
                    $registry,
                    'locate:dto',
                    'project_name@version_number@module_name',
                    'Show the full path to an existing project DTOs',
                    'blog@v1@posts'
            );
        }

        /**
         * Executes the DTO locate operation.
         *
         * Uses the {@see ModuleBuilder} to build the module and
         * {@see DtoBuilder} (via {@see EntityBuilder}) to display
         * the filesystem location of its DTOs.
         *
         * @return void
         */
        public function execute(): void
        {
            $module = ModuleBuilder::fromModuleName($this->moduleName, $this->projectName, $this->versionNumber);
            $dto = new DtoBuilder(new EntityBuilder($module));
            $dto->locate();
        }

        /**
         * Parses command arguments or prompts the user interactively.
         *
         * - If no arguments are provided, prompts the user to select a project,
         *   version, and module interactively.
         * - If arguments are provided, expects the format "project@version@module".
         *   Splits the string into project name, version number, and module name.
         *
         * @param string ...$args The command arguments (project@version@module).
         *
         * @return string|null A string containing "project@version@module" or null if skipped.
         *
         * @throws \ArgumentCountError If fewer than three arguments are provided.
         */
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
                    throw new \ArgumentCountError('At least three arguments are required.');
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
