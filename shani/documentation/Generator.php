<?php

/**
 * User application documentation generator
 * @author coder
 *
 * Created on: May 30, 2024 at 4:31:29 PM
 */

namespace shani\documentation {

    use shani\documentation\scanners\Modules;
    use shani\http\App;

    final class Generator implements \JsonSerializable
    {

        private array $modules = [];

        /**
         * Generate documentation for user application
         * @param App $app Application object
         * @param array $exclusion List of modules to exclude
         */
        public function __construct(App $app, array $exclusion = [])
        {
            $moduleDir = $app->config->root() . $app->config->moduleDir();
            $moduleCollection = Modules::scan($moduleDir, $exclusion);
            $controllerPath = $app->config->controllers();
            foreach ($moduleCollection as $modulePath) {
                $this->modules[] = new Modules(basename($modulePath), $modulePath . $controllerPath);
            }
        }

        public static function cleanComment(string $str): ?string
        {
            $comments = explode(PHP_EOL, $str);
            $size = count($comments) - 1;
            $result = ltrim($comments[1], " *\t\v\x00");
            for ($i = 2; $i < $size; $i++) {
                $result .= PHP_EOL . ltrim($comments[$i], " *\t\v\x00");
            }
            return $result;
        }

        #[\Override]
        public function jsonSerialize(): array
        {
            return [
                'modules' => $this->modules
            ];
        }
    }

}
