<?php

/**
 * HTTP host class representing application hosted in this server
 * @author coder
 *
 * Created on: Feb 12, 2024 at 8:49:03 AM
 */

namespace shani\engine\http {

    final class Host
    {

        private array $host;

        public function __construct(string $name)
        {
            $this->host = \shani\ServerConfig::host($name);
        }

        /**
         * Get current running application environment. These values are provided
         * from host configuration file.
         * @param string|null $version
         * @return string|null
         */
        public function getEnvironment(?string $version = null): ?string
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
    }

}