<?php

/**
 * Description of ResourceSelector
 * @author goddy
 *
 * Created on: May 16, 2026 at 7:08:15 PM
 */

namespace features\console {

    use features\console\builders\ModuleBuilder;
    use features\console\builders\ProjectBuilder;
    use features\console\builders\ProjectVersionBuilder;
    use features\console\helpers\ModuleName;
    use features\console\printer\ConsoleIO;
    use shani\launcher\Framework;

    final class ResourceSelector
    {

        private readonly ?string $projectName;
        private readonly ?string $versionNumber;
        private readonly ?ModuleName $moduleName;
        private readonly ?string $requestMethod;
        private readonly ?string $hostName;
        private readonly ?string $aliasName;

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
