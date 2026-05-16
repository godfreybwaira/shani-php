<?php

/**
 * Description of ListDtoCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\dto {

    use features\console\builders\ModuleBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\Formatter;
    use features\console\helpers\ModuleName;
    use features\console\helpers\ResourceName;
    use features\console\ResourceSelector;

    final class ListDtoCommand extends CommandContract
    {

        private readonly ModuleName $moduleName;
        private readonly string $projectName;
        private readonly string $versionNumber;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'list:dto', 'project_name@version_number@module_name', 'Show all existing project DTOs in a given module', 'blog@v1@posts');
        }

        public function execute(): void
        {
            $module = ModuleBuilder::fromModuleName($this->moduleName, $this->projectName, $this->versionNumber);
            $dtos = $module->getDtos();
            foreach ($dtos as $key => $dto) {
                $this->registry->addResult(Formatter::formatSentence($key + 1, $dto->dtoName));
            }
        }

        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $selector = new ResourceSelector();
                $this->projectName = $selector->selectProject();
                $this->versionNumber = $selector->selectProjectVersion();
                $this->moduleName = $selector->selectModule();
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 3) {
                    throw new \ArgumentCountError('Atleast three arguments are required.');
                }
                $this->projectName = ResourceName::create($values[0])->shortName;
                $this->versionNumber = ResourceName::create($values[1])->shortName;
                $this->moduleName = ModuleName::create($values[2]);
            }
            $parameters = $this->projectName . self::SEPARATOR . $this->versionNumber;
            return $parameters . self::SEPARATOR . $this->moduleName->originalValue;
        }
    }

}
