<?php

/**
 * Command to list all modules of a project version.
 *
 * This command retrieves all modules associated with a given project version
 * and displays them in a numbered list. It can be executed interactively
 * (via console prompts) or by passing arguments directly in the format
 * "project@version".
 *
 * If no modules are found, an exception is thrown.
 *
 * @author goddy
 * @created May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\module {

    use features\console\builders\ProjectVersionBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\Formatter;
    use features\console\helpers\ResourceName;
    use features\console\ResourceSelector;

    final class ListModulesCommand extends CommandContract
    {

        /**
         * The name of the project whose modules are being listed.
         *
         * @var string
         */
        private readonly string $projectName;

        /**
         * The version number of the project.
         *
         * @var string
         */
        private readonly string $versionNumber;

        /**
         * Initializes the command with its registry and metadata.
         *
         * @param CommandRegistry $registry The command registry instance.
         */
        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'list:module', 'project_name@version_number', 'Show all available project modules', 'blog@v1');
        }

        /**
         * Executes the list operation.
         *
         * Retrieves all modules of the specified project version using
         * {@see ProjectVersionBuilder::fromProjectName()}, formats them,
         * and adds the results to the registry.
         *
         * @return void
         *
         * @throws \InvalidArgumentException If no modules are found for the version.
         */
        public function execute(): void
        {
            $version = ProjectVersionBuilder::fromProjectName($this->projectName, $this->versionNumber);
            $modules = $version->getModules();

            if (!$modules->valid()) {
                throw new \InvalidArgumentException('No module found for version "' . $this->versionNumber . '"');
            }

            foreach ($modules as $key => $module) {
                $this->registry->addResult(Formatter::formatSentence($key + 1, $module->moduleName->directoryName));
            }
        }

        /**
         * Parses command arguments or prompts the user interactively.
         *
         * - If no arguments are provided, prompts the user to select a project
         *   and version interactively.
         * - If arguments are provided, expects the format "project@version".
         *   Splits the string into project name and version number.
         *
         * @param string ...$args The command arguments (project@version).
         *
         * @return string|null A string containing "project@version" or null if skipped.
         *
         * @throws \ArgumentCountError If fewer than two arguments are provided.
         */
        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $selector = new ResourceSelector();
                $this->projectName = $selector->selectProject();
                $this->versionNumber = $selector->selectProjectVersion();
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 2) {
                    throw new \ArgumentCountError('At least two arguments are required.');
                }
                $this->projectName = ResourceName::create($values[0])->shortName;
                $this->versionNumber = ResourceName::create($values[1])->shortName;
            }
            return $this->projectName . self::SEPARATOR . $this->versionNumber;
        }
    }

}
