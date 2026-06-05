<?php

/**
 * Description of AliasBuilder
 * @author goddy
 *
 * @since May 2, 2026 at 12:08:32 PM
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
            $this->aliasPath = self::getPath($aliasName);
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
            $filepath = self::getPath($aliasName);
            if (is_file($filepath)) {
                $vhost = VirtualHostBuilder::fromHostName(file_get_contents($filepath));
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
            if (VirtualHostBuilder::existsByName($this->aliasName)) {
                throw new \InvalidArgumentException('Host with name "' . $this->aliasName . '" already exists.');
            }
            $intext = 'Creating alias "' . $this->aliasName . '"';
            $outtext = file_put_contents($this->aliasPath, $this->vhost->metadata->hostName) !== false ? 'Success' : 'Failed';
            $progressTracker(Formatter::formatSentence($intext, $outtext));
            return $this;
        }

        #[\Override]
        public function exists(): bool
        {
            return self::existsByName($this->aliasName);
        }

        public function isBroken(): bool
        {
            return !$this->vhost->metadata->hostExists();
        }

        public static function existsByName(string $aliasName): bool
        {
            return is_file(self::getPath($aliasName));
        }

        private static function getPath(string $aliasName): string
        {
            return Framework::DIR_HOSTS . DIRECTORY_SEPARATOR . $aliasName . '.alias';
        }

        public static function getAll(): \Generator
        {
            $aliases = glob(Framework::DIR_HOSTS . '/*.alias');
            foreach ($aliases as $aliasPath) {
                $vhost = VirtualHostBuilder::fromHostName(file_get_contents($aliasPath));
                yield new AliasBuilder($vhost, basename($aliasPath, '.alias'));
            }
        }

        public static function getAllBroken(): \Generator
        {
            $aliases = glob(Framework::DIR_HOSTS . '/*.alias');
            foreach ($aliases as $aliasPath) {
                $hostName = file_get_contents($aliasPath);
                if (!VirtualHostBuilder::existsByName($hostName)) {
                    yield $aliasPath;
                }
            }
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
