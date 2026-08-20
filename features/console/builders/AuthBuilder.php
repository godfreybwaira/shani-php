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
            $filename = 'auth' . DIRECTORY_SEPARATOR . $this->type->value . '.txt';
            $className = match ($this->type) {
                AuthType::AUTH_DEFAULT => 'DefaultAuthenticator',
                AuthType::AUTH_JWT => 'JwtAuthenticator',
                AuthType::AUTH_BASIC => 'BasicAuthenticator'
            };
            $path = $this->version->createAuthenticator($filename, $className);
            if ($path === null) {
                throw new \RuntimeException('Authentication type "' . $this->type->value . '" could not be created. Check if it exists.');
            }
            $progressTracker(Formatter::formatSentence('Creating class "' . $path . '"', 'Success'));
            return $this;
        }

        public function resourceExists(): bool
        {
            return false;
        }
    }

}