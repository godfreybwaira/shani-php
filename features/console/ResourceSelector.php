<?php

namespace features\console {

    use Closure;
    use features\console\builders\ModuleBuilder;
    use features\console\builders\ProjectBuilder;
    use features\console\builders\ProjectVersionBuilder;
    use features\console\helpers\AsymmetricKeyPairType;
    use features\console\helpers\ModuleName;
    use features\console\helpers\SelectedProjectResource;
    use features\console\printer\ConsoleIO;
    use features\crypto\CryptoAlgorithm;
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

        private ?string $projectName;
        private ?string $versionNumber;
        private ?ModuleName $moduleName;
        private readonly ?string $requestMethod;
        private readonly ?string $hostName;
        private readonly ?string $aliasName;
        private readonly bool $selected;

        public function __construct()
        {
            $selector = SelectedProjectResource::getInstance();
            $this->ctName = $selector->projectName;
            $this->versionNumber = $selector->versionNumber;
            $this->moduleName = $selector->moduleName;
            $this->selected = $selector->projectName !== null;
        }

        public function wasSelected(): bool
        {
            return $this->selected;
        }

        /**
         * Displays a list of resources and prompts the user to choose one.
         *
         * @param array   $availableResources List of resources to choose from.
         * @param bool    $required           Whether selection is mandatory.
         * @param Closure $cleaner           Function to clean/format resource names.
         *
         * @return string|null The selected resource name or null if skipped.
         */
        private static function chooser(array $availableResources, bool $required, Closure $cleaner): ?string
        {
            $id = 0;
            $resources = [];
            foreach ($availableResources as $resourceName) {
                $resources[$id] = $cleaner($resourceName);
                ConsoleIO::output(($id + 1) . '. ' . $resources[$id]);
                $id++;
            }
            $choice = ConsoleIO::read('Enter your choice:', function (string $id) use ($resources, &$required) {
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
            if (!empty($this->projectName)) {
                return $this->projectName;
            }
            if ($required) {
                ConsoleIO::output('Select a project:');
            } else {
                ConsoleIO::output('Select a project or press ENTER to skip:');
            }
            $projects = array_diff(scandir(Framework::DIR_APPS), ['.', '..']);
            $this->projectName = self::chooser($projects, $required, fn(string $s) => $s);
            return $this->projectName;
        }

        public static function selectCipherAlgorithm(bool $required = true): ?string
        {
            if ($required) {
                ConsoleIO::output('Select a cipher algorithm:');
            } else {
                ConsoleIO::output('Select a cipher algorithm or press ENTER to skip:');
            }
            $algorithms = openssl_get_cipher_methods();
            return self::chooser($algorithms, $required, fn($s) => $s);
        }

        public static function selectCryptoAlgorithm(bool $required = true): ?string
        {
            if ($required) {
                ConsoleIO::output('Select a cryptographic algorithm:');
            } else {
                ConsoleIO::output('Select a cryptographic algorithm or press ENTER to skip:');
            }
            $algorithms = CryptoAlgorithm::cases();
            return self::chooser($algorithms, $required, fn(CryptoAlgorithm $s) => $s->value);
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
            if (!empty($this->versionNumber)) {
                return $this->versionNumber;
            }
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
            if (!empty($this->moduleName)) {
                return $this->moduleName;
            }
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

        public static function selectKeyPairType(bool $required = true): ?AsymmetricKeyPairType
        {
            if ($required) {
                ConsoleIO::output('Select a key pair:');
            } else {
                ConsoleIO::output('Select a key pair or press ENTER to skip:');
            }
            $keyPairs = AsymmetricKeyPairType::cases();
            $type = self::chooser($keyPairs, $required, fn(AsymmetricKeyPairType $s) => $s->value);
            return AsymmetricKeyPairType::tryFrom($type);
        }

        public static function selectCurveName(bool $required = true): ?string
        {
            if ($required) {
                ConsoleIO::output('Select a curve name:');
            } else {
                ConsoleIO::output('Select a curve name or press ENTER to skip:');
            }
            return self::chooser(openssl_get_curve_names(), $required, fn($s) => $s);
        }
    }

}
