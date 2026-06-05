<?php

/**
 * User application documentation generator
 * @author coder
 *
 * @since May 30, 2024 at 4:31:29 PM
 */

namespace features\documentation {

    use features\documentation\scanners\Modules;
    use shani\config\PathConfig;

    final class Generator implements \JsonSerializable
    {

        private array $modules = [];

        /**
         * Generate documentation for user application
         * @param PathConfig $config Path configuration object
         * @param array $exclusiveModules List of modules to exclude
         */
        public function __construct(PathConfig $config, array $exclusiveModules = [])
        {
            $moduleDir = $config->root . $config->modules;
            $moduleCollection = Modules::scan($moduleDir, $exclusiveModules);
            foreach ($moduleCollection as $modulePath) {
                $this->modules[] = new Modules(basename($modulePath), $modulePath . $config->controllers);
            }
        }

        public static function cleanComment(string $docblock): ?string
        {
            // 1. Match from the first character after the opening '/**'
            //    up until the first '@' tag or the closing '*/'.
            if (preg_match('/(?:\/\*\*[\s\*]*)(.*?)(?=\s*\*?\s*@|\*\/)/s', $docblock, $matches)) {
                $content = $matches[1];
                // 2. Remove leading asterisks and whitespace from each line
                $clean = preg_replace('/^\s*\* ?/m', '', $content);
                return trim($clean);
            }
            return null;
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
