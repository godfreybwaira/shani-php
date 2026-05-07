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

    final class VirtualHostBuilder implements LightBuilderInterface
    {

        private const CONFIG_FILE = 'config.yml';

        public readonly string $hostname;
        public readonly string $path;
        private readonly string $directory;

        public function __construct(string $hostname)
        {
            $this->hostname = $hostname;
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
                echo '[ERROR] Host "' . $this->hostname . '" does not exists.' . PHP_EOL;
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
            $newVhost = new VirtualHostBuilder($newName);
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

        private function cleanHostFile(ProjectVersionBuilder $version, string $versionTemplate): void
        {
            $placeholder = '####v1#';
            $search = [
                $placeholder, '{project_name}', '{default_version}'
            ];
            $replace = [
                $versionTemplate . PHP_EOL . $placeholder,
                $version->project->projectName, $version->versionNumber
            ];
            $content = str_replace($search, $replace, file_get_contents($this->path));
            $intext = 'Cleaning host file: ' . basename($this->path);
            $outtext = file_put_contents($this->path, $content) !== false ? 'Success' : 'Failed';
            echo Formatter::formatSentence($intext, $outtext);
        }

        public function addVersion(ProjectVersionBuilder $version): void
        {
            if (!$this->exists()) {
                echo 'Host name "' . $this->hostname . '" does not exists.' . PHP_EOL;
                return;
            }
            $filename = $version->versionName . '-' . self::CONFIG_FILE;
            $this->cleanHostFile($version, self::getVersionTemplate($version, $filename));
            $search = ['{namespace}', '{config_dir}'];
            $replace = [$version->namespace, ProjectVersionBuilder::CONFIG_DIR];
            $template = CommandContract::ASSETS . '/' . self::CONFIG_FILE;
            $content = str_replace($search, $replace, file_get_contents($template));
            $outtext = file_put_contents($this->directory . '/' . $filename, $content) !== false ? 'Success' : 'Failed';
            echo Formatter::formatSentence('Creating configuration file: ' . $filename, $outtext);
        }

        private static function getVersionTemplate(ProjectVersionBuilder $version, string $filename): string
        {
            $content = file_get_contents(CommandContract::ASSETS . '/version.yml');
            $search = ['{version_number}', '{version_name}', '{config_file}'];
            $replace = [$version->versionNumber, $version->versionName, $filename];
            return str_replace($search, $replace, $content);
        }

        #[\Override]
        public function build(): self
        {
            if (!$this->exists()) {
                mkdir($this->directory, LocalStorage::FILE_MODE, true);
                $content = file_get_contents(CommandContract::ASSETS . '/vhost.yml');
                $outtext = file_put_contents($this->path, $content) !== false ? 'Success' : 'Failed';
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
    }

}
