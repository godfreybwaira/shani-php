<?php

/**
 * Description of VirtualHostBuilder
 * @author goddy
 *
 * Created on: May 2, 2026 at 12:08:32 PM
 */

namespace features\cli\builders {

    use features\cli\Create;
    use features\cli\helpers\Formatter;
    use features\storage\LocalStorage;
    use shani\launcher\Framework;

    final class VirtualHostBuilder implements LightBuilderInterface
    {

        public readonly string $hostname;
        private readonly string $hostPath;
        private readonly ?ProjectBuilder $project;

        public function __construct(string $hostname, ProjectBuilder $project = null)
        {
            $this->hostname = $hostname;
            $this->project = $project;
            $this->hostPath = Framework::DIR_HOSTS . '/' . $this->hostname . '.yml';
        }

        private function createHost(): void
        {
            mkdir(Framework::DIR_HOSTS . '/' . $this->hostname, LocalStorage::FILE_MODE, true);
            $from = Create::ASSETS . '/vhost.yml';
            ///////////////////////////////////////////
            $intext = 'Creating host: ' . $this->hostname;
            $outtext = copy($from, $this->hostPath) ? 'Success' : 'Failed';
            echo Formatter::formatSentence($intext, $outtext);
        }

        private function copyConfigFile(): void
        {
            $filename = 'v1-config.yml';
            $path = Framework::DIR_HOSTS . '/' . $this->hostname;
            if (!is_dir($path)) {
                mkdir($path, LocalStorage::FILE_MODE, true);
            }
            $search = ['{namespace}', '{config_dir}'];
            $replace = [$this->project->namespace, ProjectBuilder::CONFIG_DIR];
            $template = Create::ASSETS . '/' . $filename;
            $content = str_replace($search, $replace, file_get_contents($template));
            ///////////////////////////////////////////
            $intext = 'Copying configuration file: ' . $filename;
            $outtext = file_put_contents($path . '/' . $filename, $content) !== false ? 'Success' : 'Failed';
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
