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
    use features\console\helpers\ProjectMetaData;
    use features\console\printer\ConsoleIO;
    use features\storage\LocalStorage;
    use shani\utils\VirtualHostMapper;

    final class VirtualHostBuilder implements LightBuilderInterface
    {

        public readonly ProjectMetaData $metadata;
        private readonly VirtualHostMapper $hostConfig;

        private function __construct(ProjectMetaData $metadata)
        {
            $this->metadata = $metadata;
        }

        public static function fromMetaData(string $projectName, string $hostName): self
        {
            return new self(new ProjectMetaData($projectName, $hostName));
        }

        #[\Override]
        public function build(\Closure $progressTracker): self
        {
            if ($this->exists()) {
                throw new \RuntimeException('Host name "' . $this->metadata->hostName . '" already exists');
            }
            mkdir($this->metadata->hostDirectory, LocalStorage::FILE_MODE, true);

            $template = file_get_contents(CommandContract::ASSETS . '/vhost.yml');
            $search = ['{project_name}', '{default_version}'];
            $replace = [$this->metadata->projectName, ProjectBuilder::DEFAULT_VERSION_NUMBER];
            $content = str_replace($search, $replace, $template);
            $outtext = file_put_contents($this->metadata->hostPath, $content) !== false ? 'Success' : 'Failed';

            $progressTracker(Formatter::formatSentence('Creating a host: ' . $this->metadata->hostName, $outtext));
            return $this;
        }

        #[\Override]
        public function exists(): bool
        {
            return $this->metadata->hostExists();
        }

        public function getConfigurations(): VirtualHostMapper
        {
            if (!isset($this->hostConfig)) {
                $this->hostConfig = VirtualHostMapper::fromArray(yaml_parse_file($this->metadata->hostPath));
            }
            return $this->hostConfig;
        }

        public function locate(): void
        {
            if ($this->exists()) {
                ConsoleIO::output($this->metadata->hostPath);
            }
        }
    }

}
