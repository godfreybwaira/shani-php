<?php

/**
 * Command to list all DTOs (Data Transfer Objects) of a project module.
 *
 * This command retrieves all DTOs associated with a given module
 * inside a project version and displays them in a numbered list.
 * It can be executed interactively (via console prompts) or by passing
 * arguments directly in the format "project@version@module".
 *
 * @author goddy
 * @created May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\dto {

    use features\console\builders\ModuleBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\Formatter;
    use features\console\helpers\ModuleName;
    use features\console\helpers\ResourceName;
    use features\console\ResourceSelector;

    final class ListDtoCommand extends CommandContract
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
         * Initializes the command with its registry and metadata.
         *
         * @param CommandRegistry $registry The command registry instance.
         */
        public function __construct(CommandRegistry $registry)
        {
            parent::__construct(
                    $registry,
                    'list:dto',
                    'project_name@version_number@module_name',
                    'Show all existing project DTOs in a given module',
                    'blog@v1@posts'
            );
        }

        /**
         * Executes the DTO listing operation.
         *
         * Uses the {@see ModuleBuilder} to build the module and retrieves
         * all DTOs. Each DTO is formatted and added to the registry.
         *
         * @return void
         */
        public function execute(): void
        {
            $module = ModuleBuilder::fromModuleName($this->moduleName, $this->projectName, $this->versionNumber);
            $dtos = $module->getDtos();

            foreach ($dtos as $key => $dto) {
                $this->registry->addResult(Formatter::formatSentence($key + 1, $dto->dtoName));
            }
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
