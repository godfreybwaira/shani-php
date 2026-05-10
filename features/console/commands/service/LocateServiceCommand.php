<?php

/**
 * Description of LocateServiceCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\service {

    use features\console\builders\ModuleBuilder;
    use features\console\builders\ProjectVersionBuilder;
    use features\console\builders\ServiceBuilder;
    use features\console\CommandContract;
    use features\console\printer\ConsoleIO;

    final class LocateServiceCommand extends CommandContract
    {

        private readonly string $moduleName;
        private readonly string $projectName;
        private readonly string $serviceName;
        private readonly string $projectVersion;

        public function __construct()
        {
            parent::__construct('locate:service', 'service_name@module_name@version_number@project_name', 'Show the full path to an existing project services', 'AuthorService@posts@v1@blog');
        }

        public function execute(): void
        {
            $version = ProjectVersionBuilder::fromVersion($this->projectVersion, $this->projectName);
            $service = new ServiceBuilder($this->serviceName, new ModuleBuilder($this->moduleName, $version));
            $service->locate();
        }

        public function parse(string ...$args): CommandContract
        {
            if (empty($args)) {
                $this->projectName = ConsoleIO::input('What is the project name?', $this->validIdentifier);
                $this->moduleName = ConsoleIO::input('What is the module name?', $this->validIdentifier);
                $this->serviceName = ConsoleIO::input('What is the service name?', $this->validIdentifier);
                $this->projectVersion = ConsoleIO::input('What is the project version number?', $this->validIdentifier);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 4) {
                    throw new \ArgumentCountError('Atleast four arguments are required.');
                }
                $this->validateIdentifier($values[0]);
                $this->validateIdentifier($values[1]);
                $this->validateIdentifier($values[2]);
                $this->validateIdentifier($values[3]);
                $this->serviceName = $values[0];
                $this->moduleName = $values[1];
                $this->projectVersion = $values[2];
                $this->projectName = $values[3];
            }
            return $this;
        }
    }

}
