<?php

/**
 * Description of ProjectBuilder
 * @author goddy
 *
 * Created on: May 2, 2026 at 12:08:32 PM
 */

namespace features\console\builders {

    use features\console\helpers\Formatter;
    use shani\launcher\Framework;
    use shani\utils\VirtualHostMapper;

    final class ProjectBuilder implements LightBuilderInterface
    {

        public readonly string $projectName;
        public readonly string $hostname;
        private readonly string $projectPath;
        public readonly VirtualHostBuilder $vhost;

        public function __construct(string $projectName, string $hostname)
        {
            $this->hostname = $hostname;
            $this->projectName = $projectName;
            $this->vhost = new VirtualHostBuilder($projectName, $hostname);
            $this->projectPath = Framework::DIR_APPS . DIRECTORY_SEPARATOR . $projectName;
        }

        private function copyCGIfiles(\Closure $tracker): void
        {
//            $destination = $this->path . $mapper->cgiDirectory;
//            $source = CommandContract::ASSETS . '/cgi';
//            if (Directory::copy($source, $destination)) {
//                $tracker($this->cleanApacheFiles($mapper, $destination));
//                $tracker($this->cleanNginxFiles($mapper, $destination));
//            }
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
                $shaniRoot, $assetDir, $sslDir, $storageDir, $this->hostname,
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
            return Formatter::formatSentence('Cleaning nginx files:', $outtext);
        }

        #[\Override]
        public function build(\Closure $progressTracker): self
        {
            if ($this->exists()) {
                throw new \RuntimeException('Project "' . $this->projectName . '" already exists');
            }
            if ($this->vhost->exists()) {
                throw new \RuntimeException('Project could not be created. Host "' . $this->hostname . '" already exists');
            }
            $this->vhost->build($progressTracker);
            $this->copyCGIfiles($progressTracker);
//            $version = new ProjectVersionBuilder($this->vhost, self::VERSION_NUMBER);
//            $version->build();
            return $this;
        }

        #[\Override]
        public function exists(): bool
        {
            return is_dir($this->projectPath);
        }
    }

}
