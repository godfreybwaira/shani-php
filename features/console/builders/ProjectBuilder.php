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

    final class ProjectBuilder implements LightBuilderInterface
    {

        private const DEFAULT_VERSION_NUMBER = 'v1';
        private const DEFAULT_VERSION_NAME = 'main';

        public readonly string $path;
        public ?VirtualHostBuilder $vhost;
        public readonly string $projectName;
        private readonly ?string $hostname;
        private ProjectVersionBuilder $version;

        public function __construct(string $projectName, string $hostname = null)
        {
            $this->hostname = $hostname;
            $this->projectName = $projectName;
            $this->path = Framework::DIR_APPS . '/' . $projectName;
            $this->version = new ProjectVersionBuilder($this, self::DEFAULT_VERSION_NUMBER, self::DEFAULT_VERSION_NAME);
            $this->vhost = $this->getVirtualHost();
        }

        private function getVirtualHost(): ?VirtualHostBuilder
        {
            if ($this->hostname !== null) {
                return new VirtualHostBuilder($this->hostname);
            }
            $hostfiles = glob(Framework::DIR_HOSTS . '/*.yml');
            foreach ($hostfiles as $file) {
                $config = yaml_parse_file($file);
                if ($config['project_name'] === $this->projectName) {
                    return new VirtualHostBuilder(basename($file, '.yml'));
                }
            }
            return null;
        }

        private function prepareCGIfiles(): void
        {
            $destination = $this->version->path . '/.cgi';
            $source = CommandContract::ASSETS . '/cgi';
            if (Directory::copy($source, $destination)) {
                $this->cleanApacheFiles($destination);
                $this->cleanNginxFiles($destination);
            }
        }

        private function cleanApacheFiles(string $destination)
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
                $this->version->config->publicBucket
            ];
            ///////////////////////////////////////////
            $apachePath = $destination . '/apache/apache.conf';
            $content = str_replace($search, $replace, file_get_contents($apachePath));
            ///////////////////////////////////////////
            $intext = 'Cleaning Apache file:';
            $outtext = file_put_contents($apachePath, $content) !== false ? 'Success' : 'Failed';
            echo Formatter::formatSentence($intext, $outtext);
        }

        private function cleanNginxFiles(string $destination)
        {
            $cgiDir = basename($destination) . '/nginx';
            $path = dirname($destination) . '/' . $cgiDir;
            $nginxFile = $path . '/nginx.conf';
            $customFile = $path . '/custom.conf';
            $appDirpath = substr($this->version->config->root, strlen(dirname(SHANI_SERVER_ROOT)) + 1);
            $assetDir = substr(Framework::DIR_ASSETS, strlen(SHANI_SERVER_ROOT) + 1);
            $storageDir = substr(Framework::DIR_STORAGE, strlen(SHANI_SERVER_ROOT) + 1);
            ///////////////////////////////////////////
            $search1 = [
                '{{app_dirpath}}', '{{asset_dir}}', '{{private_bucket}}',
                '{{storage_dir}}', '{{public_bucket}}'
            ];
            $replace1 = [
                $appDirpath . '/' . $cgiDir, $assetDir, $this->version->config->privateBucket,
                $storageDir, $this->version->config->publicBucket
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
            $this->path;
            $this->vhost?->delete();
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
                echo Formatter::placeCenter('PROJECT: ' . $this->projectName, underline: true);
                $this->vhost->build();
                $this->version->build();
                $this->prepareCGIfiles();
            }
            return $this;
        }

        #[\Override]
        public function exists(): bool
        {
            return is_dir($this->path);
        }
    }

}
