<?php

/**
 * Command to list routes of a project module.
 *
 * This command retrieves and displays all routes for a given project version.
 * If a module name is provided, only that module’s routes are listed.
 * Otherwise, routes from all modules in the version are shown.
 *
 * @author goddy
 * @created May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\routes {

    use features\console\builders\ModuleBuilder;
    use features\console\builders\ProjectVersionBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\Formatter;
    use features\console\helpers\ModuleName;
    use features\console\helpers\ResourceName;
    use features\console\ResourceSelector;

    final class ListModuleRoutesCommand extends CommandContract
    {

        /**
         * The module name within the project version (optional).
         * If null, routes from all modules are listed.
         *
         * @var ModuleName|null
         */
        private readonly ?ModuleName $moduleName;

        /**
         * The version number of the project.
         *
         * @var string
         */
        private readonly string $versionNumber;

        /**
         * The name of the project whose routes are being listed.
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
                    'list:route',
                    'project_name@version_number[@module_name]',
                    'List module routes if module name is provided, else all routes will be listed.',
                    'blog@v1@posts'
            );
        }

        /**
         * Executes the route listing operation.
         *
         * - If a module name is provided, lists routes for that module.
         * - Otherwise, lists routes for all modules in the project version.
         *
         * @return void
         */
        public function execute(): void
        {
            $this->registry->addResult(Formatter::formatSentence('#. ROUTE', 'REQUEST METHOD', separator: ' '));
            if ($this->moduleName == null) {
                $this->getAll();
            } else {
                $module = ModuleBuilder::fromModuleName($this->moduleName, $this->projectName, $this->versionNumber);
                $this->getOne($module);
            }
        }

        /**
         * Lists routes for a single module.
         *
         * @param ModuleBuilder $module  The module to list routes for.
         * @param int           $counter The starting counter for numbering routes.
         *
         * @return int The updated counter after listing routes.
         *
         * @throws \InvalidArgumentException If no routes are found for the module.
         */
        private function getOne(ModuleBuilder $module, int $counter = 1): int
        {
            $routes = $module->getRoutes();
            if (!$routes->valid()) {
                throw new \InvalidArgumentException('No routes found for module "' . $module->moduleName->originalValue . '"');
            }
            foreach ($routes as $name => $method) {
                $this->registry->addResult(Formatter::formatSentence(($counter++) . '. ' . $name, $method));
            }
            return $counter;
        }

        /**
         * Lists routes for all modules in the project version.
         *
         * @return void
         */
        private function getAll(): void
        {
            $counter = 1;
            $version = ProjectVersionBuilder::fromProjectName($this->projectName, $this->versionNumber);
            $modules = $version->getModules();
            foreach ($modules as $module) {
                $counter = $this->getOne($module, $counter);
            }
        }

        /**
         * Parses command arguments or prompts the user interactively.
         *
         * - If no arguments are provided, prompts the user to select a project,
         *   version, and optionally a module.
         * - If arguments are provided, expects the format "project@version[@module]".
         *   Splits the string into project name, version number, and optional module name.
         *
         * @param string ...$args The command arguments (project@version[@module]).
         *
         * @return string|null A string containing "project@version@module" or null if skipped.
         *
         * @throws \ArgumentCountError If fewer than two arguments are provided.
         */
        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $selector = new ResourceSelector();
                $this->projectName = $selector->selectProject();
                $this->versionNumber = $selector->selectProjectVersion();
                $this->moduleName = $selector->selectModule(false);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 2) {
                    throw new \ArgumentCountError('At least two arguments are required.');
                }
                $this->projectName = ResourceName::create($values[0])->shortName;
                $this->versionNumber = ResourceName::create($values[1])->shortName;
                $this->moduleName = !empty($values[2]) ? ModuleName::create($values[2]) : null;
            }
            $parameters = $this->projectName . self::SEPARATOR . $this->versionNumber;
            return $parameters . self::SEPARATOR . $this->moduleName->originalValue;
        }
    }

}
