<?php

/**
 * Description of Host
 * @author coder
 *
 * Created on: Feb 12, 2024 at 8:49:03 AM
 */

namespace shani\engine\http {

    final class Host implements \shani\contracts\Handler
    {

        private ?array $host;
        private static \shani\contracts\Cacheable $config;

        public function __construct(string $name)
        {
            $this->host = self::$config->get($name);
            if (empty($this->host)) {
                $this->host = \shani\ServerConfig::host($name);
                self::$config->replace($name, $this->host);
            }
        }

        public function getConfig(?string $version = null): ?string
        {
            if ($version === null) {
                $env = $this->host['VERSIONS'][$this->host['DEFAULT_VERSION']];
                return $env['ENVIRONMENTS'][$env['ACTIVE_ENVIRONMENT']];
            }
            if (!empty($this->host['VERSIONS'][$version])) {
                $env = $this->host['VERSIONS'][$version];
                return $env['ENVIRONMENTS'][$env['ACTIVE_ENVIRONMENT']];
            }
            return null;
        }

        public static function setHandler($handler): void
        {
            self::$config = $handler;
        }
    }

}