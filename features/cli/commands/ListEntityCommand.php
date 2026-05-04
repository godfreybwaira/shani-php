<?php

/**
 * Description of ListEntityCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\cli\commands {

    use features\cli\builders\ModuleBuilder;
    use features\cli\builders\ProjectBuilder;
    use features\cli\CommandContract;
    use features\cli\helpers\Formatter;

    final class ListEntityCommand extends CommandContract
    {

        private readonly string $moduleName;
        private readonly string $projectName;

        public function __construct()
        {
            parent::__construct('list:entity', 'module_name@project_name', 'Show all the existing project entities (models) in a given module', 'posts@blog');
        }

        public function execute(): void
        {
            $project = new ProjectBuilder($this->projectName);
            $module = new ModuleBuilder($this->moduleName, $project);

            $entities = $module->getEntities();
            echo Formatter::placeCenter('List of Entities (in ' . $this->moduleName . ' module)', underline: true);
            foreach ($entities as $key => $entity) {
                echo Formatter::formatSentence($key + 1, $entity->entityName);
            }
        }

        public function parse(string ...$args): CommandContract
        {
            $values = explode(self::SEPARATOR, $args[0]);
            if (count($values) > 3) {
                throw new \ArgumentCountError('Only three arguments are required.');
            }
            $this->validateHostName($values[0]);
            $this->validateHostName($values[1]);
            $this->moduleName = $values[0];
            $this->projectName = $values[1];
            return $this;
        }
    }

}
