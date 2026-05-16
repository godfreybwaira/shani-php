<?php

/**
 * Command to locate project controllers.
 *
 * This command shows the filesystem path to the controllers of a specific module
 * within a given project version. It can be executed interactively (via console prompts)
 * or by passing arguments directly in the format "project@version@module@method".
 *
 * @author goddy
 * @created May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\controller {

    use features\console\builders\ControllerBuilder;
    use features\console\builders\ModuleBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\ModuleName;
    use features\console\helpers\ResourceName;
    use features\console\ResourceSelector;

    final class LocateControllerCommand extends CommandContract
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
         * The HTTP request method associated with the controller (e.g., GET, POST).
         *
         * @var string
         */
        private readonly string $requestMethod;

        /**
         * Initializes the command with its registry and metadata.
         *
         * @param CommandRegistry $registry The command registry instance.
         */
        public function __construct(CommandRegistry $registry)
        {
            parent::__construct(
                    $registry,
                    'locate:controller',
                    'project_name@version_number@module_name@request_method',
                    'Show the full path to an existing project controllers',
                    'blog@v1@posts@get'
            );
        }

        /**
         * Executes the controller locate operation.
         *
         * Uses the {@see ModuleBuilder} to build the module and
         * {@see ControllerBuilder} to display the filesystem location of its controller.
         *
         * @return void
         */
        public function execute(): void
        {
            $module = ModuleBuilder::fromModuleName($this->moduleName, $this->projectName, $this->versionNumber);
            $controller = new ControllerBuilder($module, $this->requestMethod);
            $controller->locate();
        }

        /**
         * Parses command arguments or prompts the user interactively.
         *
         * - If no arguments are provided, prompts the user to select a project,
         *   version, module, and request method interactively.
         * - If arguments are provided, expects the format "project@version@module@method".
         *   Splits the string into project name, version number, module name, and request method.
         *
         * @param string ...$args The command arguments (project@version@module@method).
         *
         * @return string|null A string containing "project@version@module@method" or null if skipped.
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
                $this->requestMethod = $selector->selectRequestMethod();
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 4) {
                    throw new \ArgumentCountError('At least four arguments are required.');
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
