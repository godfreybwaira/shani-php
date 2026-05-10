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
    use features\console\builders\ProjectVersionBuilder;
    use features\console\CommandContract;
    use features\console\printer\ConsoleIO;

    final class CreateEntityCommand extends CommandContract
    {

        private readonly string $projectName;
        private readonly string $projectVersion;
        private readonly string $moduleName;
        private readonly string $entityName;

        public function __construct()
        {
            parent::__construct('create:entity', 'entity_name@module_name@version_number@project_name', 'Create a custom entity and it\'s associated DTO', 'Review@posts@v1@blog');
        }

        public function execute(): void
        {
            $version = ProjectVersionBuilder::fromVersion($this->projectVersion, $this->projectName);
            $entity = new EntityBuilder($this->entityName, new ModuleBuilder($this->moduleName, $version));
            $entity->build();
        }

        public function parse(string ...$args): CommandContract
        {
            if (empty($args)) {
                $this->projectName = ConsoleIO::input('What is the project name?', $this->validIdentifier);
                $this->moduleName = ConsoleIO::input('What is the module name?', $this->validIdentifier);
                $this->entityName = ConsoleIO::input('What is the entity (a.k.a model) name?', $this->validIdentifier);
                $this->projectVersion = ConsoleIO::input('What is the project version number?', $this->validIdentifier);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 3) {
                    throw new \ArgumentCountError('Atleast three arguments are required.');
                }
                $this->validateIdentifier($values[0]);
                $this->validateIdentifier($values[1]);
                $this->validateIdentifier($values[2]);
                $this->validateIdentifier($values[3]);
                $this->entityName = $values[0];
                $this->moduleName = $values[1];
                $this->projectVersion = $values[2];
                $this->projectName = $values[3];
            }
            return $this;
        }
    }

}
