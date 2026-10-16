<?php

/**
 * Description of AuthBuilder
 * @author goddy
 *
 * @since v1.0: Aug 18, 2026 at 2:07:35 PM
 */

namespace features\console\builders {

    use features\console\helpers\AuthType;
    use features\console\helpers\Formatter;

    final class AuthBuilder implements LightBuilderInterface
    {

        private readonly ProjectVersionBuilder $version;
        private readonly AuthType $type;

        public function __construct(ProjectVersionBuilder $version, AuthType $type)
        {
            $this->version = $version;
            $this->type = $type;
        }

        public function build(\Closure $progressTracker): LightBuilderInterface
        {
            if ($this->type === AuthType::AUTH_ALL) {
                return $this->createAllAuthTypes($progressTracker);
            }
            $path = self::createAuthMthod($this->version, $this->type);
            if ($path === null) {
                throw new \RuntimeException('Authentication type "' . $this->type->value . '" could not be created. Check if it exists.');
            }
            $progressTracker(Formatter::formatSentence('Creating class "' . $path . '"', 'Success'));
            return $this;
        }

        private function createAllAuthTypes(\Closure $progressTracker): LightBuilderInterface
        {
            array_map(function (AuthType $type)use (&$progressTracker) {
                if ($type !== AuthType::AUTH_ALL) {
                    $path = self::createAuthMthod($this->version, $type);
                    if ($path === null) {
                        $progressTracker(Formatter::formatSentence('Creating Authentication type "' . $type->value . '"', 'Failed'));
                    } else {
                        $progressTracker(Formatter::formatSentence('Creating class "' . $path . '"', 'Success'));
                    }
                }
            }, AuthType::cases());
            return $this;
        }

        private static function createAuthMthod(ProjectVersionBuilder $version, AuthType $type): ?string
        {
            $filename = 'auth' . DIRECTORY_SEPARATOR . $type->value . '.txt';
            $className = match ($type) {
                AuthType::AUTH_DEFAULT => 'DefaultAuthenticator',
                AuthType::AUTH_JWT => 'JwtAuthenticator',
                AuthType::AUTH_BASIC => 'BasicAuthenticator',
                default => throw new \RuntimeException('Unsupported authentication type "' . $type->value . '"')
            };
            return $version->createAuthenticator($filename, $className);
        }

        public function resourceExists(): bool
        {
            return false;
        }
    }

}