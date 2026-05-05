<?php

/**
 * Description of LocateDtoCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\dto {

    use features\console\builders\ModuleBuilder;
    use features\console\builders\ProjectBuilder;
    use features\console\CommandContract;

    final class LocateDtoCommand extends CommandContract
    {

        private readonly string $moduleName;
        private readonly string $projectName;

        public function __construct()
        {
            parent::__construct('dto:locate', 'module_name@project_name', 'Show the full path to an existing project DTOs', 'posts@blog');
        }

        public function execute(): void
        {
            $project = new ProjectBuilder($this->projectName);
            $module = new ModuleBuilder($this->moduleName, $project);
            $module->locateDto();
        }

        public function parse(string ...$args): CommandContract
        {
            $values = explode(self::SEPARATOR, $args[0]);
            if (count($values) < 2) {
                throw new \ArgumentCountError('Atleast two arguments are required.');
            }
            $this->validateHostName($values[0]);
            $this->validateHostName($values[1]);
            $this->moduleName = $values[0];
            $this->projectName = $values[1];
            return $this;
        }
    }

}
