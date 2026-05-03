<?php

/**
 * Description of Delete
 * @author goddy
 *
 * Created on: May 1, 2026 at 9:34:23 AM
 */

namespace features\cli {

    use features\cli\builders\AliasBuilder;
    use features\cli\builders\ProjectBuilder;
    use features\utils\Directory;

    final class Delete
    {

        public static function project(string $projectName): void
        {
            echo 'Deleting project "' . $projectName . '"' . PHP_EOL;
            $project = new ProjectBuilder($projectName);
            Directory::delete($project->config->root);
        }

        public static function module(string $params): void
        {

        }

        public static function controller(string $params): void
        {

        }

        public static function alias(string $aliasName): void
        {
            $alias = new AliasBuilder($aliasName);
            $alias->delete();
        }

        public static function vhost(string $hostname): void
        {
            $alias = new builders\VirtualHostBuilder($hostname);
            $alias->delete();
        }
    }

}
