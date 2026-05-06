<?php

/**
 * Description of ListServiceCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\service {

    use features\console\builders\ModuleBuilder;
    use features\console\builders\ProjectBuilder;
    use features\console\CommandContract;
    use features\console\helpers\Formatter;
    use features\console\printer\ConsoleIO;

    final class ListServiceCommand extends CommandContract
    {

        private readonly string $moduleName;
        private readonly string $projectName;

        public function __construct()
        {
            parent::__construct('list:service', 'module_name@project_name', 'Show the list of existing project services', 'posts@blog');
        }

        public function execute(): void
        {
            $project = new ProjectBuilder($this->projectName);
            $module = new ModuleBuilder($this->moduleName, $project);
            $services = $module->getServices();
            echo Formatter::placeCenter('List of Services (in ' . $this->moduleName . ' module)', underline: true);
            foreach ($services as $key => $service) {
                echo Formatter::formatSentence($key + 1, $service->serviceName);
            }
        }

        public function parse(string ...$args): CommandContract
        {
            if (empty($args)) {
                $this->projectName = ConsoleIO::input('What is the project name?', $this->validIdentifier);
                $this->moduleName = ConsoleIO::input('What is the module name?', $this->validIdentifier);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 3) {
                    throw new \ArgumentCountError('Atleast three arguments are required.');
                }
                $this->validateHostName($values[0]);
                $this->validateHostName($values[1]);
                $this->moduleName = $values[0];
                $this->projectName = $values[1];
            }
            return $this;
        }
    }

}
