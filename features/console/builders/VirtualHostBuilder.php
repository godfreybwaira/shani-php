<?php

/**
 * Description of VirtualHostBuilder
 * @author goddy
 *
 * Created on: May 2, 2026 at 12:08:32 PM
 */

namespace features\console\builders {

    use features\console\CommandContract;
    use features\console\helpers\Formatter;
    use features\storage\LocalStorage;
    use features\utils\Directory;
    use shani\launcher\Framework;
    use shani\utils\VirtualHostMapper;

    final class VirtualHostBuilder implements LightBuilderInterface
    {

        public const CONFIG_FILE = 'config.yml';

        public readonly string $directory;
        public readonly string $hostname;
        public readonly string $path;
        private readonly string $projectName;

        public function __construct(string $projectName, string $hostname)
        {
            $this->hostname = $hostname;
            $this->projectName = $projectName;
            $this->directory = Framework::DIR_HOSTS . '/' . $this->hostname;
            $this->path = $this->directory . '.yml';
        }

        public function locate(): void
        {
            echo $this->exists() ? $this->path : null;
        }

        public function delete(): void
        {
            if (!$this->exists()) {
                echo Formatter::formatSentence('Host "' . $this->hostname . '" does not exists', 'Failed');
                return;
            }
            if (Directory::delete($this->directory)) {
                $aliases = $this->getAliases();
                foreach ($aliases as $alias) {
                    $alias->delete();
                }
            }
            $intext = 'Deleting host: ' . $this->hostname;
            $outtext = unlink($this->path) ? 'Success' : 'Failed';
            echo Formatter::formatSentence($intext, $outtext);
        }

        public function rename(string $newName): void
        {
            if (!$this->exists()) {
                echo '[ERROR] Host "' . $this->hostname . '" does not exists.' . PHP_EOL;
                return;
            }
            $newVhost = new VirtualHostBuilder($this->projectName, $newName);
            if ($newVhost->exists()) {
                throw new \RuntimeException('Host name "' . $newName . '" already exists.');
            }
            $aliases = $this->getAliases();
            $intext = 'Renaming a host from "' . $this->hostname . '" to "' . $newVhost->hostname . '"';
            $renamed = rename($this->directory, $newVhost->directory) && rename($this->path, $newVhost->path);
            $outtext = $renamed ? 'Success' : 'Failed';
            if ($renamed) {
                foreach ($aliases as $alias) {
                    file_put_contents($alias->aliasPath, $newVhost->hostname);
                }
            }
            echo Formatter::formatSentence($intext, $outtext);
        }

        public function getAliases(): array
        {
            $aliases = [];
            $files = glob(Framework::DIR_HOSTS . '/*.alias');
            foreach ($files as $file) {
                if (file_get_contents($file) === $this->hostname) {
                    $aliases[] = new AliasBuilder(basename($file, '.alias'), $this->hostname);
                }
            }
            return $aliases;
        }

        public static function getConfigFilename(string $versionNumber, ?string $versionName = null): string
        {
            return ($versionName ?? $versionNumber) . '-' . self::CONFIG_FILE;
        }

        public function registerVersion(string $versionNumber, string $versionName = null): void
        {
            $vname = $versionName ?? $versionNumber;
            $search = ['{version_number}', '{version_name}', '{config_file}'];
            $replace = [$versionNumber, $vname, self::getConfigFilename($versionNumber, $versionName)];
            $template = str_replace($search, $replace, file_get_contents(CommandContract::ASSETS . '/version.yml'));
            ////////////////////////
            $placeholder = '####v1#';
            $content = str_replace($placeholder, $template . PHP_EOL . $placeholder, file_get_contents($this->path));
            $intext = 'Registering version "' . $versionNumber . '"';
            $outtext = file_put_contents($this->path, $content) !== false ? 'Success' : 'Failed';
            echo Formatter::formatSentence($intext, $outtext);
        }

        #[\Override]
        public function build(\Closure $progressTracker): self
        {
            if (!$this->exists()) {
                mkdir($this->directory, LocalStorage::FILE_MODE, true);
                $template = file_get_contents(CommandContract::ASSETS . '/vhost.yml');
                $search = ['{project_name}', '{default_version}'];
                $replace = [$this->projectName, ProjectBuilder::VERSION_NUMBER];
                $content = str_replace($search, $replace, $template);
                $outtext = file_put_contents($this->path, $content) !== false ? 'Success' : 'Failed';
                $this->registerVersion(ProjectBuilder::VERSION_NUMBER);
                echo Formatter::formatSentence('Creating host: ' . $this->hostname, $outtext);
            } else {
                echo Formatter::formatSentence('Host name "' . $this->hostname . '" already exists', 'Failed');
            }
            return $this;
        }

        #[\Override]
        public function exists(): bool
        {
            return is_file($this->path);
        }

        public function getConfigurations(): VirtualHostMapper
        {
            return VirtualHostMapper::fromArray(yaml_parse_file($this->path));
        }
    }

}
