<?php

/**
 * Description of ProjectBuilder
 * @author goddy
 *
 * Created on: May 2, 2026 at 12:08:32 PM
 */

namespace features\console\builders {

    use features\console\CommandContract;
    use features\console\helpers\Formatter;
    use features\console\helpers\ProjectMetaData;
    use features\console\printer\ConsoleIO;
    use features\utils\Directory;
    use shani\launcher\Framework;
    use shani\utils\VirtualHostMapper;

    final class ProjectBuilder implements LightBuilderInterface
    {

        public const DEFAULT_VERSION_NUMBER = 'v1';

        public readonly ProjectMetaData $metadata;
        public readonly VirtualHostBuilder $vhost;

        private function __construct(ProjectMetaData $metadata)
        {
            $this->metadata = $metadata;
            $this->vhost = VirtualHostBuilder::fromMetaData($metadata->projectName, $metadata->hostName);
        }

        public static function fromMetaData(string $projectName, string $hostName): self
        {
            return new self(new ProjectMetaData($projectName, $hostName));
        }

        private function copyCGIfiles(\Closure $tracker): void
        {
            $mapper = $this->vhost->getConfigurations();
            $destination = $this->metadata->projectDirectory . $mapper->cgiDirectory;
            $source = CommandContract::ASSETS . '/cgi';
            if (Directory::copy($source, $destination)) {
                $tracker($this->cleanApacheFiles($mapper, $destination));
                $tracker($this->cleanNginxFiles($mapper, $destination));
            }
        }

        private function cleanApacheFiles(VirtualHostMapper $mapper, string $destination): string
        {
            $shaniRoot = basename(SHANI_SERVER_ROOT);
            $assetDir = substr(Framework::DIR_ASSETS, strlen(SHANI_SERVER_ROOT) + 1);
            $storageDir = substr(Framework::DIR_STORAGE, strlen(SHANI_SERVER_ROOT) + 1);
            $sslDir = substr(Framework::DIR_SSL, strlen(SHANI_SERVER_ROOT) + 1);
            ///////////////////////////////////////////
            $search = [
                '{{shani_root}}', '{{asset_dir}}', '{{ssl_dir}}', '{{storage_dir}}',
                '{{domain_name}}', '{{public_bucket}}'
            ];
            $replace = [
                $shaniRoot, $assetDir, $sslDir, $storageDir, $this->metadata->hostName,
                $mapper->publicBucket
            ];
            ///////////////////////////////////////////
            $apachePath = $destination . '/apache/apache.conf';
            $content = str_replace($search, $replace, file_get_contents($apachePath));
            ///////////////////////////////////////////
            $outtext = file_put_contents($apachePath, $content) !== false ? 'Success' : 'Failed';
            return Formatter::formatSentence('Cleaning Apache file:', $outtext);
        }

        private function cleanNginxFiles(VirtualHostMapper $mapper, string $destination): string
        {
            $cgiDir = basename($destination) . '/nginx';
            $path = dirname($destination) . DIRECTORY_SEPARATOR . $cgiDir;
            $nginxFile = $path . '/nginx.conf';
            $customFile = $path . '/custom.conf';
            $appDirpath = substr($this->metadata->projectDirectory, strlen(dirname(SHANI_SERVER_ROOT)) + 1);
            $assetDir = substr(Framework::DIR_ASSETS, strlen(SHANI_SERVER_ROOT) + 1);
            $storageDir = substr(Framework::DIR_STORAGE, strlen(SHANI_SERVER_ROOT) + 1);
            ///////////////////////////////////////////
            $search1 = [
                '{{app_dirpath}}', '{{asset_dir}}', '{{private_bucket}}',
                '{{storage_dir}}', '{{public_bucket}}'
            ];
            $replace1 = [
                $appDirpath . '/' . $cgiDir, $assetDir, $mapper->privateBucket,
                $storageDir, $mapper->publicBucket
            ];
            $nginxContent = str_replace($search1, $replace1, file_get_contents($nginxFile));
            file_put_contents($nginxFile, $nginxContent);
            $search = ['{{shani_root}}', '{{domain_name}}'];
            $replace = [basename(SHANI_SERVER_ROOT), $this->metadata->hostName];
            $content = str_replace($search, $replace, file_get_contents($customFile));
            ///////////////////////////////////////////
            $outtext = file_put_contents($customFile, $content) !== false ? 'Success' : 'Failed';
            return Formatter::formatSentence('Cleaning nginx files:', $outtext);
        }

        #[\Override]
        public function build(\Closure $progressTracker): self
        {
            if ($this->exists()) {
                throw new \RuntimeException('Project "' . $this->metadata->projectName . '" already exists');
            }
            $this->vhost->build($progressTracker);

            $version = new ProjectVersionBuilder($this->vhost, self::DEFAULT_VERSION_NUMBER);
            $version->build($progressTracker);

            $this->copyCGIfiles($progressTracker);
            return $this;
        }

        #[\Override]
        public function exists(): bool
        {
            return $this->metadata->projectExists();
        }

        public function locate(): void
        {
            if ($this->exists()) {
                ConsoleIO::output($this->metadata->projectDirectory);
            }
        }

        public static function fromName(string $projectName): ProjectBuilder
        {
            $hostfiles = glob(Framework::DIR_HOSTS . '/*.yml');
            foreach ($hostfiles as $file) {
                $config = yaml_parse_file($file);
                if ($config['project_name'] === $projectName) {
                    return ProjectBuilder::fromMetaData($projectName, basename($file, '.yml'));
                }
            }
            throw new \InvalidArgumentException('Project "' . $projectName . '" does not exists');
        }
    }

}
