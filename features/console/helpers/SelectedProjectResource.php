<?php

/**
 * Description of SelectedProjectResource
 * @author goddy
 *
 * @since v1.0: Jun 11, 2026 at 12:12:49 PM
 */

namespace features\console\helpers {

    use shani\launcher\Framework;

    final class SelectedProjectResource
    {

        private const SELECTION_FILE = Framework::DIR_SERVER_STORAGE . DIRECTORY_SEPARATOR . '.selp';

        private static SelectedProjectResource $instance;
        public readonly ?string $projectName;
        public readonly ?string $versionNumber;
        public readonly ?ModuleName $moduleName;

        private function __construct(?string $projectName, ?string $versionNumber, ?ModuleName $moduleName)
        {
            $this->projectName = $projectName;
            $this->versionNumber = $versionNumber;
            $this->moduleName = $moduleName;
        }

        /**
         * Select the current working project.
         * @param string|null $projectName Selected project name
         * @param string|null $versionNumber Selected project version number
         * @param ModuleName|null $moduleName Selected project module name
         * @return bool true on success, false otherwise.
         */
        public static function select(?string $projectName, ?string $versionNumber, ?ModuleName $moduleName): bool
        {
            $content = json_encode([
                'project' => $projectName,
                'version' => $versionNumber,
                'module' => $moduleName?->originalValue,
            ]);
            return file_put_contents(self::SELECTION_FILE, $content) !== false;
        }

        public static function getInstance(): SelectedProjectResource
        {
            if (!isset(self::$instance)) {
                if (file_exists(self::SELECTION_FILE)) {
                    $data = json_decode(file_get_contents(self::SELECTION_FILE), true);
                    $moduleName = !empty($data['module']) ? ModuleName::create($data['module']) : null;
                    self::$instance = new SelectedProjectResource($data['project'], $data['version'], $moduleName);
                } else {
                    self::$instance = new SelectedProjectResource(null, null, null);
                }
            }
            return self::$instance;
        }

        public static function deselect(): bool
        {
            return file_exists(self::SELECTION_FILE) && unlink(self::SELECTION_FILE);
        }
    }

}
