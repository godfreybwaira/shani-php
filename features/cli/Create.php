<?php

/**
 * Description of Create
 * @author goddy
 *
 * Created on: May 1, 2026 at 9:34:23 AM
 */

namespace features\cli {

    use shani\launcher\Framework;

    final class Create
    {

        public const SEPARATOR = '@';
        public const ASSETS = Framework::DIR_FEATURES . '/cli/assets';

        public static function project(string $params): void
        {
            $values = explode(self::SEPARATOR, $params);
            if (count($values) < 2) {
                echo 'Please follow the following pattern: project_name@host_name' . PHP_EOL;
                return;
            }
            $controllerName = 'Account';
            $moduleName = 'users';
            $projectName = $values[0];
            $project = new builders\ProjectBuilder($projectName, $moduleName, $controllerName);
            $project->setHostName($values[1])->build();
        }

        public static function module(string $params): void
        {
            $values = explode(self::SEPARATOR, $params);
            if (count($values) < 2) {
                echo 'Please follow the following pattern: module_name@project_name' . PHP_EOL;
                return;
            }
            $moduleName = $values[0];
            $projectName = $values[1];
            $project = new builders\ProjectBuilder($projectName, $moduleName);
            $project->build();
        }

        public static function controller(string $params): void
        {
            $values = explode(self::SEPARATOR, $params);
            if (count($values) < 3) {
                echo 'Please follow the following pattern: controller_name@module_name@project_name' . PHP_EOL;
                return;
            }
            $controllerName = $values[0];
            $moduleName = $values[1];
            $projectName = $values[2];
            $project = new builders\ProjectBuilder($projectName, $moduleName, $controllerName);
            $project->build();
        }

        public static function alias(string $params): void
        {
            $values = explode(self::SEPARATOR, $params);
            if (count($values) < 2) {
                echo 'Please follow the following pattern: alias@hostname' . PHP_EOL;
                return;
            }
            $aliasName = $values[0];
            $hostname = $values[1];
            $alias = new builders\AliasBuilder($aliasName, $hostname);
            $alias->build();
        }

        public static function vhost(string $params): void
        {
            $values = explode(self::SEPARATOR, $params);
            if (count($values) < 2) {
                echo 'Please follow the following pattern: project_name@hostname' . PHP_EOL;
                return;
            }
            $projectName = $values[0];
            $hostname = $values[1];
            $project = new builders\ProjectBuilder($projectName);
            $vhost = new builders\VirtualHostBuilder($hostname, $project);
            $vhost->build();
        }
    }

}
