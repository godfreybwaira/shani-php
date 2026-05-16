<?php

/**
 * Command to create a new project module.
 *
 * This command generates a new module within a given project version.
 * It can be executed interactively (via console prompts) or by passing
 * arguments directly in the format "project@version@module".
 *
 * @author goddy
 * @created May 3, 2026 at 8:59:28 PM
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

        /**
         * The name of the project where the module will be created.
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
         * The module name to create within the project version.
         *
         * @var ModuleName
         */
        private readonly ModuleName $moduleName;

        /**
         * Initializes the command with its registry and metadata.
         *
         * @param CommandRegistry $registry The command registry instance.
         */
        public function __construct(CommandRegistry $registry)
        {
            parent::__construct(
                    $registry,
                    'create:module',
                    'project_name@version_number@module_name',
                    'Create a new project module',
                    'blog@v1@posts'
            );
        }

        /**
         * Executes the module creation operation.
         *
         * Uses the {@see ModuleBuilder} to build the module and logs
         * the result in the registry.
         *
         * @return void
         */
        public function execute(): void
        {
            $module = ModuleBuilder::fromModuleName($this->moduleName, $this->projectName, $this->versionNumber);
            $module->build(fn($s) => $this->registry->addResult($s));
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
            return $this->projectName . self::SEPARATOR . $this->versionNumber . self::SEPARATOR . $this->moduleName->className;
        }
    }

}
