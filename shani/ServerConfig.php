<?php

/**
 * Description of ServerConfig
 * @author coder
 *
 * Created on: Mar 6, 2024 at 4:06:33 PM
 */

namespace shani {

    final class ServerConfig
    {

        private const PATH = SERVER_ROOT . '/config';
        public const SSL_DIR = self::PATH . '/ssl';

        public static function mime(string $extension): ?string
        {
            $mime = yaml_parse_file(self::PATH . '/mime.yml');
            return $mime[$extension] ?? null;
        }

        public static function template(string $version): ?string
        {
            try {
                $cnf = yaml_parse_file(self::PATH . '/template.yml');
                return $cnf['VERSION'][$version];
            } catch (\RuntimeException $exc) {
                echo 'Template version "' . $version . '" not found.';
                return null;
            }
        }

        public static function host(string $name): array
        {
            try {
                return yaml_parse_file(self::PATH . '/hosts/' . $name . '.yml');
            } catch (\RuntimeException $exc) {
                echo 'Host "' . $name . '" not found.';
                return [];
            }
        }

        public static function server(): array
        {
            return yaml_parse_file(self::PATH . '/server.yml');
        }
    }

}
