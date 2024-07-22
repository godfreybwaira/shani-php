<?php

/**
 * Description of ServerConfig
 * @author coder
 *
 * Created on: Mar 6, 2024 at 4:06:33 PM
 */

namespace shani {

    use shani\engine\core\Definitions;

    final class ServerConfig
    {

        public static function mime(string $extension): ?string
        {
            $mime = yaml_parse_file(Definitions::DIR_CONFIG . '/mime.yml');
            return $mime[$extension] ?? null;
        }

        public static function host(string $name): array
        {
            try {
                return yaml_parse_file(Definitions::DIR_HOSTS . '/' . $name . '.yml');
            } catch (\RuntimeException $exc) {
                echo 'Host "' . $name . '" not found.';
                return [];
            }
        }

        public static function server(): array
        {
            return yaml_parse_file(Definitions::DIR_CONFIG . '/server.yml');
        }
    }

}
