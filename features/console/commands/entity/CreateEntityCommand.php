<?php

/**
 * Description of CreateEntityCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\entity {

    use features\console\builders\EntityBuilder;
    use features\console\builders\ModuleBuilder;
    use features\console\builders\ProjectBuilder;
    use features\console\CommandContract;

    final class CreateEntityCommand extends CommandContract
    {

        private readonly string $projectName;
        private readonly string $moduleName;
        private readonly string $entityName;

        public function __construct()
        {
            parent::__construct('entity:create', 'entity_name@module_name@project_name', 'Create a custom entity and it\'s associated DTO', 'Review@posts@blog');
        }

        public function execute(): void
        {
            $module = new ModuleBuilder($this->moduleName, new ProjectBuilder($this->projectName));
            $entity = new EntityBuilder($this->entityName, $module);
            $entity->build();
        }

        public function parse(string ...$args): CommandContract
        {
            $values = explode(self::SEPARATOR, $args[0]);
            if (count($values) < 2) {
                throw new \ArgumentCountError('Atleast two arguments are required.');
            }
            $this->validateIdentifier($values[0]);
            $this->validateIdentifier($values[1]);
            $this->validateIdentifier($values[2]);
            $this->moduleName = $values[0];
            $this->entityName = $values[1];
            $this->projectName = $values[2];
            return $this;
        }
    }

}
