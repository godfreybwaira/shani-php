<?php

/**
 * Description of AliasBuilder
 * @author goddy
 *
 * Created on: May 2, 2026 at 12:08:32 PM
 */

namespace features\cli\builders {

    use features\cli\helpers\Formatter;
    use shani\launcher\Framework;

    final class AliasBuilder implements LightBuilderInterface
    {

        public readonly string $aliasPath;
        private readonly string $aliasName;
        private readonly ?string $hostname;
        private readonly string $hostPath;

        public function __construct(string $aliasName, string $hostname = null)
        {
            $this->aliasName = $aliasName;
            $this->aliasPath = Framework::DIR_HOSTS . '/' . $this->aliasName . '.alias';
            $this->hostname = $hostname ?? $this->getHostName();
            $this->hostPath = Framework::DIR_HOSTS . '/' . $this->hostname . '.yml';
        }

        public function delete(): void
        {
            $intext = 'Deleting alias "' . $this->aliasName . '"';
            $outtext = $this->exists() && unlink($this->aliasPath) ? 'Success' : 'Failed';
            echo Formatter::formatSentence($intext, $outtext);
        }

        public function rename(string $newName): void
        {
            if (!$this->exists()) {
                throw new \RuntimeException('Alias "' . $this->aliasName . '" does not exists.');
            }
            $newAlias = new AliasBuilder($newName, $this->hostname);
            if ($newAlias->exists()) {
                throw new \RuntimeException('Alias name "' . $newName . '" already exists.');
            }
            $intext = 'Renaming alias from "' . $this->aliasName . '" to "' . $newAlias->aliasName . '"';
            $outtext = rename($this->aliasPath, $newAlias->aliasPath) ? 'Success' : 'Failed';
            echo Formatter::formatSentence($intext, $outtext);
        }

        private function getHostName(): ?string
        {
            if ($this->exists()) {
                $content = file_get_contents($this->aliasPath);
                return $content !== false ? $content : null;
            }
            return null;
        }

        #[\Override]
        public function build(): self
        {
            if (!is_file($this->hostPath)) {
                echo 'Could not create alias "' . $this->aliasName . '", host "' . $this->hostname . '" does not exists.';
                return $this;
            }
            if (!$this->exists()) {
                $intext = 'Creating alias "' . $this->aliasName . '" for host "' . $this->hostname . '"';
                $outtext = file_put_contents($this->aliasPath, $this->hostname) !== false ? 'Success' : 'Failed';
                echo Formatter::formatSentence($intext, $outtext);
            } else {
                echo 'Alias "' . $this->aliasName . '" already exists.' . PHP_EOL;
            }
            return $this;
        }

        #[\Override]
        public function exists(): bool
        {
            return is_file($this->aliasPath);
        }
    }

}
