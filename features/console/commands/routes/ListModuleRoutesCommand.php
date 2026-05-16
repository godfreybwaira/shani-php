<?php

/**
 * Description of ListModuleRoutesCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\routes {

    use features\console\builders\ModuleBuilder;
    use features\console\builders\ProjectVersionBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\Formatter;
    use features\console\helpers\ModuleName;
    use features\console\helpers\ResourceName;
    use features\console\printer\ConsoleIO;

    final class ListModuleRoutesCommand extends CommandContract
    {

        private readonly ?ModuleName $moduleName;
        private readonly string $versionNumber;
        private readonly string $projectName;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'list:route', 'project_name@version_number[@module_name]', 'List module routes if module name is provided, else all routes will be listed.', 'blog@v1@posts');
        }

        public function execute(): void
        {
            $this->registry->addResult(Formatter::formatSentence('#. ROUTE', 'REQUEST METHOD', separator: ' '));
            if ($this->moduleName == null) {
                $this->getAll();
            } else {
                $module = ModuleBuilder::fromModuleName($this->moduleName, $this->projectName, $this->versionNumber);
                $this->getOne($module);
            }
        }

        private function getOne(ModuleBuilder $module, int $counter = 1): int
        {
            $routes = $module->getRoutes();
            if (!$routes->valid()) {
                throw new \InvalidArgumentException('No routes found for module "' . $module->moduleName->originalValue . '"');
            }
            foreach ($routes as $name => $method) {
                $this->registry->addResult(Formatter::formatSentence(($counter++) . '. ' . $name, $method));
            }
            return $counter;
        }

        private function getAll(): void
        {
            $counter = 1;
            $version = ProjectVersionBuilder::fromProjectName($this->projectName, $this->versionNumber);
            $modules = $version->getModules();
            foreach ($modules as $module) {
                $counter = $this->getOne($module, $counter);
            }
        }

        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $this->projectName = ConsoleIO::read('What is the project name?', $this->validIdentifier);
                $this->versionNumber = ConsoleIO::read('What is the project version number?', $this->validIdentifier);
                $name = ConsoleIO::read('Enter a module name or press enter to ignore', fn(string $s) => empty($s) || ($this->validIdentifier)($s));
                $this->moduleName = !empty($name) ? ModuleName::create($name) : null;
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 2) {
                    throw new \ArgumentCountError('Atleast two arguments are required.');
                }
                $this->projectName = ResourceName::create($values[0])->shortName;
                $this->versionNumber = ResourceName::create($values[1])->shortName;
                $this->moduleName = !empty($values[2]) ? ModuleName::create($values[2]) : null;
            }
            $parameters = $this->projectName . self::SEPARATOR . $this->versionNumber;
            return $parameters . self::SEPARATOR . $this->moduleName->originalValue;
        }
    }

}
