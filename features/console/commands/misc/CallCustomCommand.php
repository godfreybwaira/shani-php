<?php

/**
 * Description of CallCustomCommand
 * @author goddy
 *
 * @since v1.0: Aug 8, 2026 at 12:12:19 PM
 */

namespace features\console\commands\misc {

    use features\console\builders\ProjectVersionBuilder;
    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\ModuleName;
    use features\console\helpers\ResourceName;
    use features\console\printer\ConsoleIO;
    use features\console\printer\PrintedText;
    use features\console\ResourceSelector;

    final class CallCustomCommand extends CommandContract
    {

        /**
         * The project command label
         *
         * @var string
         */
        private readonly string $label;

        /**
         * The name of the project for which the version is being created.
         *
         * @var string
         */
        private readonly string $projectName;

        /**
         * The version number to call command from.
         *
         * @var string
         */
        private readonly string $versionNumber;

        /**
         * List of user arguments
         *
         * @var array
         */
        private readonly array $argsList;

        /**
         * Constructor.
         *
         * Registers the command with the given registry.
         *
         * @param CommandRegistry $registry The command registry instance.
         */
        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'command:call', 'project@version@mylabel', 'Call a user defined custom command', 'demo@v1@user:create');
        }

        public function execute(): void
        {
            $version = ProjectVersionBuilder::fromProjectName($this->projectName, $this->versionNumber);
            if ($version->callUserCommand($this->label, $this->argsList)) {
                $this->registry->addResult(PrintedText::success('Command completed successfully.'));
            } else {
                $this->registry->addResult(PrintedText::error('Command failed.'));
            }
        }

        public function parse(string ...$args): ?string
        {
            if (empty($args)) {
                $selector = new ResourceSelector();
                $this->projectName = $selector->selectProject(true);
                $this->versionNumber = $selector->selectProjectVersion(true);
                $this->label = ConsoleIO::read('Enter the command label:', fn(string $s) => true);
                $list = ConsoleIO::read('Enter optional arguments or press enter to ignore:', fn(string $s) => true);
                $this->argsList = empty($list) ? [] : explode(' ', $list);
            } else {
                $values = explode(self::SEPARATOR, $args[0]);
                if (count($values) < 3) {
                    throw new \ArgumentCountError('At least three arguments are required.');
                }
                $this->projectName = ResourceName::create($values[0])->shortName;
                $this->versionNumber = ModuleName::create($values[1])->directoryName;
                $this->label = $values[2];
                $this->argsList = array_slice($args, 1);
            }
            return $this->projectName . self::SEPARATOR . $this->versionNumber . self::SEPARATOR . $this->label;
        }
    }

}
