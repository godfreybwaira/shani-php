<?php

/**
 * Description of ListDtoCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\dto {

    use features\console\builders\ModuleBuilder;
    use features\console\builders\ProjectVersionBuilder;
    use features\console\CommandContract;
    use features\console\helpers\Formatter;
    use features\console\printer\ConsoleIO;

    final class ListDtoCommand extends CommandContract
    {

        private readonly string $moduleName;
        private readonly string $projectName;
        private readonly string $projectVersion;

        public function __construct()
        {
            parent::__construct('list:dto', 'module_name@version_number@project_name', 'Show all existing project DTOs in a given module', 'posts@v1@blog');
        }

        public function execute(): void
        {
            $version = ProjectVersionBuilder::fromVersion($this->projectVersion, $this->projectName);
            $module = new ModuleBuilder($this->moduleName, $version);
            $dtos = $module->getDtos();
            echo Formatter::placeCenter('List of DTOs (in ' . $this->moduleName . ' module)', underline: true);
            foreach ($dtos as $key => $dto) {
                echo Formatter::formatSentence($key + 1, $dto->dtoName);
            }
        }

        public function parse(string ...$args): CommandContract
        {
            if (empty($args)) {
                $this->projectName = ConsoleIO::input('What is the project name?', $this->validIdentifier);
                $this->moduleName = ConsoleIO::input('What is the module name?', $this->validIdentifier);
                $this->projectVersion = ConsoleIO::input('What is the project version number?', $this->validIdentifier);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 3) {
                    throw new \ArgumentCountError('Atleast three arguments are required.');
                }
                $this->validateIdentifier($values[0]);
                $this->validateIdentifier($values[1]);
                $this->validateIdentifier($values[2]);
                $this->moduleName = $values[0];
                $this->projectVersion = $values[1];
                $this->projectName = $values[2];
            }
            return $this;
        }
    }

}
