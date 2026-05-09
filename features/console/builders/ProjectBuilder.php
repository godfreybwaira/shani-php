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
    use features\utils\Directory;
    use shani\launcher\Framework;
    use shani\utils\VirtualHostMapper;

    final class ProjectBuilder implements LightBuilderInterface
    {

        public const VERSION_NUMBER = 'v1';

        public readonly string $projectName;
        public readonly string $hostname;
        public readonly VirtualHostBuilder $vhost;
        private readonly string $path;

        public function __construct(string $projectName, string $hostname)
        {
            $this->hostname = $hostname;
            $this->projectName = $projectName;
            $this->vhost = new VirtualHostBuilder($hostname, $this);
            $this->path = Framework::DIR_APPS . '/' . $projectName;
        }

        private function copyCGIfiles(): void
        {
            $mapper = VirtualHostBuilder::getConfigurations($this->vhost->path);
            $destination = $this->path . $mapper->cgiDirectory;
            $source = CommandContract::ASSETS . '/cgi';
            if (Directory::copy($source, $destination)) {
                $this->cleanApacheFiles($mapper, $destination);
                $this->cleanNginxFiles($mapper, $destination);
            }
        }

        private function cleanApacheFiles(VirtualHostMapper $mapper, string $destination)
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
                $shaniRoot, $assetDir, $sslDir, $storageDir, $this->hostname,
                $mapper->publicBucket
            ];
            ///////////////////////////////////////////
            $apachePath = $destination . '/apache/apache.conf';
            $content = str_replace($search, $replace, file_get_contents($apachePath));
            ///////////////////////////////////////////
            $intext = 'Cleaning Apache file:';
            $outtext = file_put_contents($apachePath, $content) !== false ? 'Success' : 'Failed';
            echo Formatter::formatSentence($intext, $outtext);
        }

        private function cleanNginxFiles(VirtualHostMapper $mapper, string $destination)
        {
            $cgiDir = basename($destination) . '/nginx';
            $path = dirname($destination) . '/' . $cgiDir;
            $nginxFile = $path . '/nginx.conf';
            $customFile = $path . '/custom.conf';
            $appDirpath = substr($this->path, strlen(dirname(SHANI_SERVER_ROOT)) + 1);
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
            $replace = [basename(SHANI_SERVER_ROOT), $this->hostname];
            $content = str_replace($search, $replace, file_get_contents($customFile));
            ///////////////////////////////////////////
            $outtext = file_put_contents($customFile, $content) !== false ? 'Success' : 'Failed';
            $intext = 'Cleaning nginx files:';
            echo Formatter::formatSentence($intext, $outtext);
        }

        public function delete(): void
        {
            $this->vhost->delete();
            $resultText = Directory::delete($this->path) ? 'Success' : 'Failed';
            echo Formatter::formatSentence('Deleting project "' . $this->projectName . '"', $resultText);
        }

        public function locate(): void
        {
            echo $this->exists() ? $this->path : null;
        }

        #[\Override]
        public function build(): self
        {
            if ($this->exists()) {
                echo Formatter::formatSentence('Project "' . $this->projectName . '" already exists', 'Failed');
            } else if ($this->vhost->exists()) {
                echo Formatter::formatSentence('Host name "' . $this->hostname . '" already exists', 'Failed');
            } else {
                echo Formatter::placeCenter('Building Project "' . $this->projectName . '"', underline: true);
                $this->vhost->build();
                $this->copyCGIfiles();
                $version = new ProjectVersionBuilder($this->vhost, self::VERSION_NUMBER);
                $version->build();
            }
            return $this;
        }

        #[\Override]
        public function exists(): bool
        {
            return is_dir($this->path);
        }

        public static function fromName(string $projectName): ProjectBuilder
        {
            $hostfiles = glob(Framework::DIR_HOSTS . '/*.yml');
            foreach ($hostfiles as $file) {
                $config = yaml_parse_file($file);
                if ($config['project_name'] === $projectName) {
                    return new ProjectBuilder($projectName, basename($file, '.yml'));
                }
            }
            throw new \InvalidArgumentException('Project "' . $projectName . '" does not exists');
        }
    }

}
