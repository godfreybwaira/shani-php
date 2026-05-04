<?php

/**
 * Description of VirtualHostBuilder
 * @author goddy
 *
 * Created on: May 2, 2026 at 12:08:32 PM
 */

namespace features\cli\builders {

    use features\cli\CommandContract;
    use features\cli\helpers\Formatter;
    use features\storage\LocalStorage;
    use features\utils\Directory;
    use shani\launcher\Framework;

    final class VirtualHostBuilder implements LightBuilderInterface
    {

        public readonly string $hostname;
        private readonly string $hostPath;
        public readonly string $hostDirectory;
        private readonly ?ProjectBuilder $project;

        public function __construct(string $hostname, ProjectBuilder $project = null)
        {
            $this->hostname = $hostname;
            $this->project = $project;
            $this->hostDirectory = Framework::DIR_HOSTS . '/' . $this->hostname;
            $this->hostPath = $this->hostDirectory . '.yml';
        }

        public function delete(): void
        {
            if (!$this->exists()) {
                echo '[ERROR] Host "' . $this->hostname . '" does not exists.' . PHP_EOL;
                return;
            }
            ///////////////////////////////////////////
            if (Directory::delete($this->hostDirectory)) {
                $aliases = $this->getAliases();
                foreach ($aliases as $alias) {
                    $alias->delete();
                }
            }
            ///////////////////////////////////////////
            $intext = 'Deleting host: ' . $this->hostname;
            $outtext = unlink($this->hostPath) ? 'Success' : 'Failed';
            echo Formatter::formatSentence($intext, $outtext);
        }

        public function rename(string $newName): void
        {
            if (!$this->exists()) {
                echo '[ERROR] Host "' . $this->hostname . '" does not exists.' . PHP_EOL;
                return;
            }
            $newVhost = new VirtualHostBuilder($newName);
            ///////////////////////////////////////////
            if ($newVhost->exists()) {
                throw new \RuntimeException('Host name "' . $newName . '" already exists.');
            }
            $aliases = $this->getAliases();
            $intext = 'Renaming a host from "' . $this->hostname . '" to "' . $newVhost->hostname . '"';
            $renamed = rename($this->hostDirectory, $newVhost->hostDirectory) && rename($this->hostPath, $newVhost->hostPath);
            $outtext = $renamed ? 'Success' : 'Failed';
            ///////////////////////////////////////////
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

        private function createHost(): void
        {
            mkdir($this->hostDirectory, LocalStorage::FILE_MODE, true);
            $from = CommandContract::ASSETS . '/vhost.yml';
            ///////////////////////////////////////////
            $intext = 'Creating host: ' . $this->hostname;
            $outtext = copy($from, $this->hostPath) ? 'Success' : 'Failed';
            echo Formatter::formatSentence($intext, $outtext);
        }

        private function copyConfigFile(): void
        {
            $configFile = 'v1-config.yml';
            if (!is_dir($this->hostDirectory)) {
                mkdir($this->hostDirectory, LocalStorage::FILE_MODE, true);
            }
            $search = ['{namespace}', '{config_dir}'];
            $replace = [$this->project->namespace, ProjectBuilder::CONFIG_DIR];
            $template = CommandContract::ASSETS . '/' . $configFile;
            $content = str_replace($search, $replace, file_get_contents($template));
            ///////////////////////////////////////////
            $intext = 'Copying configuration file: ' . $configFile;
            $outtext = file_put_contents($this->hostDirectory . '/' . $configFile, $content) !== false ? 'Success' : 'Failed';
            echo Formatter::formatSentence($intext, $outtext);
        }

        #[\Override]
        public function build(): self
        {
            if (!$this->exists()) {
                $this->createHost();
                $this->copyConfigFile();
            } else {
                echo 'Host name "' . $this->hostname . '" is already taken.' . PHP_EOL;
            }
            return $this;
        }

        #[\Override]
        public function exists(): bool
        {
            return is_file($this->hostPath);
        }
    }

}
