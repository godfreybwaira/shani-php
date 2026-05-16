<?php

/**
 * Command to run application version tests.
 *
 * This command executes automated tests for a specific project version.
 * It can be run interactively (via console prompts) or by passing arguments
 * directly in the format "project@version".
 *
 * @author goddy
 * @created May 15, 2026 at 3:24:05 PM
 */

namespace features\console\commands\version {

    use features\console\builders\ProjectVersionBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\ModuleName;
    use features\console\helpers\ResourceName;
    use features\console\printer\PrintedText;
    use features\console\ResourceSelector;

    final class TestVersionCommand extends CommandContract
    {

        /**
         * The name of the project whose version is being tested.
         *
         * @var string
         */
        private readonly string $projectName;

        /**
         * The version number of the project to test.
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
            parent::__construct(
                    $registry,
                    'test:version',
                    'project_name@version_number',
                    'Run application version test',
                    'blog@v1'
            );
        }

        /**
         * Executes the version test.
         *
         * - Runs the test for the specified project version.
         * - Throws an exception if the test fails.
         * - Logs a success message if the test passes.
         *
         * @return void
         *
         * @throws \Exception If the test fails.
         */
        public function execute(): void
        {
            $version = ProjectVersionBuilder::fromProjectName($this->projectName, $this->versionNumber);

            if (!$version->runTest()) {
                throw new \Exception('Test Failed');
            }

            $this->registry->addResult(PrintedText::success('Test Passed'));
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
                $this->versionNumber = ModuleName::create($values[1])->directoryName;
            }
            return $this->projectName . self::SEPARATOR . $this->versionNumber;
        }
    }

}
