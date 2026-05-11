<?php

/**
 * Description of LocateEntityCommand
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

    final class LocateEntityCommand extends CommandContract
    {

        private readonly string $moduleName;
        private readonly string $projectName;
        private readonly string $entityName;
        private readonly string $projectVersion;

        public function __construct()
        {
            parent::__construct('locate:entity', 'entity_name@module_name@version_number@project_name', 'Show the full path to an existing project entities (models)', 'ReviewEntity@posts@v1@blog');
        }

        public function execute(): void
        {
            $version = ProjectVersionBuilder::fromVersion($this->projectVersion, $this->projectName);
            $entity = new EntityBuilder($this->entityName, new ModuleBuilder($this->moduleName, $version));
            $entity->locate();
        }

        public function parse(string ...$args): CommandContract
        {
            if (empty($args)) {
                $this->projectName = ConsoleIO::input('What is the project name?', $this->validIdentifier);
                $this->moduleName = ConsoleIO::input('What is the module name?', $this->validIdentifier);
                $this->projectVersion = ConsoleIO::input('What is the project version number?', $this->validIdentifier);
                $this->entityName = ConsoleIO::input('What is the entity name?', $this->validIdentifier);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 4) {
                    throw new \ArgumentCountError('Atleast four arguments are required.');
                }
                self::validateIdentifier($values[0]);
                self::validateIdentifier($values[1]);
                self::validateIdentifier($values[2]);
                self::validateIdentifier($values[3]);
                $this->entityName = $values[0];
                $this->moduleName = $values[1];
                $this->projectVersion = $values[2];
                $this->projectName = $values[3];
            }
            return $this;
        }
    }

}
