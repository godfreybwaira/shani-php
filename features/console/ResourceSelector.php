<?php

namespace features\console {

    use features\console\builders\ModuleBuilder;
    use features\console\builders\ProjectBuilder;
    use features\console\builders\ProjectVersionBuilder;
    use features\console\helpers\ModuleName;
    use features\console\printer\ConsoleIO;
    use shani\launcher\Framework;

    /**
     * Handles interactive resource selection for console operations.
     *
     * Provides methods to select projects, hosts, aliases, versions, modules,
     * and HTTP request methods through console prompts.
     *
     * @author goddy
     * @created May 16, 2026 at 7:08:15 PM
     */
    final class ResourceSelector
    {

        private readonly ?string $projectName;
        private readonly ?string $versionNumber;
        private readonly ?ModuleName $moduleName;
        private readonly ?string $requestMethod;
        private readonly ?string $hostName;
        private readonly ?string $aliasName;

        /**
         * Displays a list of resources and prompts the user to choose one.
         *
         * @param array   $availableResources List of resources to choose from.
         * @param bool    $required           Whether selection is mandatory.
         * @param \Closure $cleaner           Function to clean/format resource names.
         *
         * @return string|null The selected resource name or null if skipped.
         */
        private static function chooser(array $availableResources, bool $required, \Closure $cleaner): ?string
        {
            $id = 0;
            $resources = [];
            foreach ($availableResources as $resourceName) {
                $resources[$id] = $cleaner($resourceName);
                ConsoleIO::output(($id + 1) . '. ' . $resources[$id]);
                $id++;
            }
            $choice = ConsoleIO::read('Enter your choice:', function (string $id) use (&$resources, &$required) {
                if ($required || !empty($id)) {
                    $value = preg_match('/^[0-9]+$/', $id) === 1 ? (int) $id : null;
                    return $value !== null && isset($resources[$value - 1]);
                }
                return true;
            });
            return empty($choice) ? null : $resources[(int) $choice - 1];
        }

        /**
         * Prompts the user to select a project.
         *
         * @param bool $required Whether selection is mandatory.
         *
         * @return string|null The selected project name or null if skipped.
         */
        public function selectProject(bool $required = true): ?string
        {
            if ($required) {
                ConsoleIO::output('Select a project:');
            } else {
                ConsoleIO::output('Select a project or press ENTER to skip:');
            }
            $projects = array_diff(scandir(Framework::DIR_APPS), ['.', '..']);
            $this->projectName = self::chooser($projects, $required, fn(string $s) => $s);
            return $this->projectName;
        }

        /**
         * Prompts the user to select a host configuration.
         *
         * @param bool $required Whether selection is mandatory.
         *
         * @return string|null The selected host name or null if skipped.
         */
        public function selectHost(bool $required = true): ?string
        {
            if ($required) {
                ConsoleIO::output('Select a host:');
            } else {
                ConsoleIO::output('Select a host or press ENTER to skip:');
            }
            $hosts = glob(Framework::DIR_HOSTS . '/*.yml');
            $this->hostName = self::chooser($hosts, $required, fn(string $s) => basename($s, '.yml'));
            return $this->hostName;
        }

        /**
         * Prompts the user to select a host alias.
         *
         * @param bool $required Whether selection is mandatory.
         *
         * @return string|null The selected alias name or null if skipped.
         */
        public function selectAlias(bool $required = true): ?string
        {
            if ($required) {
                ConsoleIO::output('Select a host alias:');
            } else {
                ConsoleIO::output('Select a host alias or press ENTER to skip:');
            }
            $aliases = glob(Framework::DIR_HOSTS . '/*.alias');
            $this->aliasName = self::chooser($aliases, $required, fn(string $s) => basename($s, '.alias'));
            return $this->aliasName;
        }

        /**
         * Prompts the user to select a project version.
         *
         * @param bool $required Whether selection is mandatory.
         *
         * @return string|null The selected version number or null if skipped.
         */
        public function selectProjectVersion(bool $required = true): ?string
        {
            if ($required) {
                ConsoleIO::output('Select the project version:');
            } else {
                ConsoleIO::output('Select the project version or press ENTER to skip:');
            }
            $generator = ProjectBuilder::fromName($this->projectName)->getVersions();
            $versions = [];
            foreach ($generator as $version) {
                $versions[] = $version->versionNumber;
            }
            $this->versionNumber = self::chooser($versions, $required, fn(string $s) => $s);
            return $this->versionNumber;
        }

        /**
         * Prompts the user to select a module within the project version.
         *
         * @param bool $required Whether selection is mandatory.
         *
         * @return ModuleName|null The selected module name or null if skipped.
         */
        public function selectModule(bool $required = true): ?ModuleName
        {
            if ($required) {
                ConsoleIO::output('Select the module:');
            } else {
                ConsoleIO::output('Select the module or press ENTER to skip:');
            }
            $modules = [];
            $version = ProjectVersionBuilder::fromProjectName($this->projectName, $this->versionNumber);
            $generator = $version->getModules();
            foreach ($generator as $module) {
                $modules[] = $module->moduleName;
            }
            $name = self::chooser($modules, $required, fn(ModuleName $s) => $s->originalValue);
            $this->moduleName = ModuleName::create($name);
            return $this->moduleName;
        }

        /**
         * Prompts the user to select an HTTP request method.
         *
         * @param bool $required Whether selection is mandatory.
         *
         * @return string|null The selected request method or null if skipped.
         */
        public function selectRequestMethod(bool $required = true): ?string
        {
            if ($required) {
                ConsoleIO::output('Select the HTTP request method:');
            } else {
                ConsoleIO::output('Select the HTTP request method or press ENTER to skip:');
            }
            $methods = [];
            $module = ModuleBuilder::fromModuleName($this->moduleName, $this->projectName, $this->versionNumber);
            $controllers = $module->getControllers();
            foreach ($controllers as $controller) {
                $methods[] = strtoupper($controller->requestMethod);
            }
            $this->requestMethod = self::chooser($methods, $required, fn(string $s) => $s);
            return $this->requestMethod;
        }
    }

}
