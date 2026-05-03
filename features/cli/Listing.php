<?php

/**
 * Description of Listing
 * @author goddy
 *
 * Created on: May 2, 2026 at 10:41:03 AM
 */

namespace features\cli {

    use features\cli\builders\ModuleBuilder;
    use features\cli\builders\ProjectBuilder;
    use features\cli\helpers\Formatter;
    use shani\launcher\Framework;

    final class Listing
    {

        public static function project(): void
        {
            echo 'Listing all projects' . PHP_EOL;
            $projects = array_diff(scandir(Framework::DIR_APPS), ['.', '..']);
            foreach ($projects as $key => $name) {
                echo Formatter::formatSentence($key - 1, $name);
            }
            echo 'Done' . PHP_EOL;
        }

        public static function module(string $projectName): void
        {
            echo 'Listing all project modules: ' . $projectName . PHP_EOL;
            $project = new ProjectBuilder($projectName);
            if (!$project->exists()) {
                echo 'Project "' . $projectName . '" not exists.' . PHP_EOL;
                return;
            }
            $modules = $project->getModules();
            foreach ($modules as $key => $module) {
                echo Formatter::formatSentence($key + 1, $module->moduleName);
            }
            echo 'Done' . PHP_EOL;
        }

        public static function controller(string $params): void
        {
            $values = explode(Create::SEPARATOR, $params);
            if (count($values) < 2) {
                echo 'Please follow the following pattern: module_name@project_name' . PHP_EOL;
                return;
            }
            $moduleName = $values[0];
            $projectName = $values[1];
            echo 'Listing all module controller: ' . $projectName . PHP_EOL;
            $project = new ProjectBuilder($projectName);
            $module = new ModuleBuilder($moduleName, $project);
            if (!$module->exists()) {
                echo 'Module "' . $moduleName . '" not exists.' . PHP_EOL;
                return;
            }
            $controllers = $module->getControllers();
            foreach ($controllers as $key => $controller) {
                $outtext = '[' . strtoupper($controller->requestMethod) . '] ' . $controller->controllerName;
                echo Formatter::formatSentence($key + 1, $outtext);
            }
            echo 'Done' . PHP_EOL;
        }

        public static function alias(string $hostName): void
        {
            echo 'Listing all host aliases: ' . $hostName . PHP_EOL;
            if (!is_file(Framework::DIR_HOSTS . '/' . $hostName . '.yml')) {
                echo 'Host "' . $hostName . '" not exists.' . PHP_EOL;
                return;
            }
            $aliases = glob(Framework::DIR_HOSTS . '/*.alias');
            if (empty($aliases)) {
                echo 'No alias found for host "' . $hostName . '"' . PHP_EOL;
                return;
            }
            foreach ($aliases as $key => $name) {
                if (file_get_contents($name) === $hostName) {
                    echo Formatter::formatSentence($key + 1, basename($name, '.alias'));
                }
            }
            echo 'Done' . PHP_EOL;
        }
    }

}
