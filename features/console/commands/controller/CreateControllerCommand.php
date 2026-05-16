<?php

/**
 * Command to create a new project controller.
 *
 * This command generates a controller inside a specified module of a project version.
 * Alongside the controller, it also scaffolds its associated service, DTO, entity,
 * view, and language file. It can be executed interactively (via console prompts)
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
    use features\console\printer\ConsoleIO;
    use features\console\ResourceSelector;

    final class CreateControllerCommand extends CommandContract
    {

        /**
         * The name of the project where the controller will be created.
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
         * The module name within the project version.
         *
         * @var ModuleName
         */
        private readonly ModuleName $moduleName;

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
                    'create:controller',
                    'project_name@version_number@module_name@request_method',
                    'Create a new project controller, its associated service, dto, entity, view and language file',
                    'blog@v1@posts@get'
            );
        }

        /**
         * Executes the controller creation operation.
         *
         * Uses the {@see ModuleBuilder} to build the module and
         * {@see ControllerBuilder} to generate the new controller and its
         * associated resources. The result is logged in the registry.
         *
         * @return void
         */
        public function execute(): void
        {
            $module = ModuleBuilder::fromModuleName($this->moduleName, $this->projectName, $this->versionNumber);
            $controller = new ControllerBuilder($module, $this->requestMethod);
            $controller->build(fn($s) => $this->registry->addResult($s));
        }

        /**
         * Parses command arguments or prompts the user interactively.
         *
         * - If no arguments are provided, prompts the user to select a project,
         *   version, and module interactively, then asks for the request method.
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
                $this->requestMethod = ConsoleIO::read('What is the request method?', $this->validIdentifier);
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
