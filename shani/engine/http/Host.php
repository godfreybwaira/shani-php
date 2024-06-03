<?php

/**
 * Description of Host
 * @author coder
 *
 * Created on: Feb 12, 2024 at 8:49:03 AM
 */

namespace shani\engine\http {

    final class Host implements \shani\adaptor\Handler
    {

        private ?array $host;
        private static \shani\adaptor\Cacheable $config;

        public function __construct(string $name)
        {
            $this->host = self::$config->get($name);
            if (empty($this->host)) {
                $this->host = \shani\Config::host($name);
                self::$config->replace($name, $this->host);
            }
        }

        public function getConfig(?string $version = null): ?string
        {
            if ($version === null) {
                return $this->host['CONFIG_CLASS'][$this->host['DEFAULT_VERSION']];
            }
            return $this->host['CONFIG_CLASS'][$version] ?? null;
        }

        public static function setHandler($handler): void
        {
            self::$config = $handler;
        }
    }

}