<?php

/**
 * Description of CreateControllerCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\cli\commands {

    use features\cli\builders\ProjectBuilder;
    use features\cli\CommandContract;

    final class CreateControllerCommand extends CommandContract
    {

        private readonly string $projectName;
        private readonly string $moduleName;
        private readonly string $controllerName;

        public function __construct()
        {
            parent::__construct('create:controller', 'controller_name@module_name@project_name', 'Creating a new project controller, view and language file', 'review@posts@blog');
        }

        public function execute(): void
        {
            $project = new ProjectBuilder($this->projectName, $this->moduleName, $this->controllerName);
            $project->build();
        }

        public function parse(string ...$args): CommandContract
        {
            $values = explode(self::SEPARATOR, $args[0]);
            if (count($values) < 3) {
                throw new \ArgumentCountError('Atleast three arguments is required.');
            }
            $this->validateIdentifier($values[0]);
            $this->validateIdentifier($values[1]);
            $this->validateIdentifier($values[2]);
            $this->controllerName = $values[0];
            $this->moduleName = $values[1];
            $this->projectName = $values[2];
            return $this;
        }
    }

}
