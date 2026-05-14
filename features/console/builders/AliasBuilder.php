<?php

/**
 * Description of AliasBuilder
 * @author goddy
 *
 * Created on: May 2, 2026 at 12:08:32 PM
 */

namespace features\console\builders {

    use features\console\helpers\Formatter;
    use features\console\printer\ConsoleIO;
    use shani\launcher\Framework;

    final class AliasBuilder implements LightBuilderInterface
    {

        private readonly string $aliasPath;
        public readonly string $aliasName;
        public readonly VirtualHostBuilder $vhost;

        public function __construct(VirtualHostBuilder $vhost, string $aliasName)
        {
            $this->vhost = $vhost;
            $this->aliasName = $aliasName;
            $this->aliasPath = Framework::DIR_HOSTS . DIRECTORY_SEPARATOR . $this->aliasName . '.alias';
        }

        public function locate(): void
        {
            if ($this->exists()) {
                ConsoleIO::output($this->aliasPath);
            }
        }

        public function delete(\Closure $progressTracker): void
        {
            $intext = 'Deleting alias "' . $this->aliasName . '"';
            $outtext = $this->exists() && unlink($this->aliasPath) ? 'Success' : 'Failed';
            $progressTracker(Formatter::formatSentence($intext, $outtext));
        }

        public function rename(string $newName, \Closure $progressTracker): void
        {
            if (!$this->exists()) {
                throw new \InvalidArgumentException('Alias "' . $this->aliasName . '" does not exists.');
            }
            $newAlias = new AliasBuilder($this->vhost, $newName);
            if ($newAlias->exists()) {
                throw new \InvalidArgumentException('Alias name "' . $newName . '" already exists.');
            }
            $intext = 'Renaming alias from "' . $this->aliasName . '" to "' . $newAlias->aliasName . '"';
            $outtext = rename($this->aliasPath, $newAlias->aliasPath) ? 'Success' : 'Failed';
            $progressTracker(Formatter::formatSentence($intext, $outtext));
        }

        public static function fromAliasName(string $aliasName): AliasBuilder
        {
            $filename = Framework::DIR_HOSTS . DIRECTORY_SEPARATOR . $aliasName . '.alias';
            if (is_file($filename)) {
                $vhost = VirtualHostBuilder::fromHostName(file_get_contents($filename));
                return new AliasBuilder($vhost, $aliasName);
            }
            throw new \InvalidArgumentException('Host alias "' . $aliasName . '" does not exists');
        }

        #[\Override]
        public function build(\Closure $progressTracker): self
        {
            if (!$this->vhost->exists()) {
                $errorMsg = 'Could not create alias "' . $this->aliasName . '", host "';
                $errorMsg .= $this->vhost->metadata->hostName . '" does not exists.';
                throw new \RuntimeException($errorMsg);
            }
            if ($this->exists()) {
                throw new \InvalidArgumentException('Alias "' . $this->aliasName . '" already exists.');
            }
            $intext = 'Creating alias "' . $this->aliasName . '"';
            $outtext = file_put_contents($this->aliasPath, $this->vhost->metadata->hostName) !== false ? 'Success' : 'Failed';
            $progressTracker(Formatter::formatSentence($intext, $outtext));
            return $this;
        }

        #[\Override]
        public function exists(): bool
        {
            return is_file($this->aliasPath);
        }

        public static function getAllByHostName(string $hostName): \Generator
        {
            $aliases = glob(Framework::DIR_HOSTS . '/*.alias');
            $vhost = VirtualHostBuilder::fromHostName($hostName);
            foreach ($aliases as $aliasName) {
                if (file_get_contents($aliasName) === $hostName) {
                    yield new AliasBuilder($vhost, basename($aliasName, '.alias'));
                }
            }
        }
    }

}
