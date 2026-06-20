<?php

/**
 * Command to display help information for console commands.
 *
 * This command provides usage instructions and descriptions for all available
 * commands in the application. If a specific command name is provided, it shows
 * detailed help for that command (syntax, example, description). If no argument
 * is given, it lists all commands with their descriptions.
 *
 * @author goddy
 * @created May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands\misc {

    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\Formatter;
    use shani\launcher\Framework;

    final class HelpCommand extends CommandContract
    {

        /**
         * The command name provided by the user for detailed help.
         * If null, lists all commands.
         *
         * @var string|null
         */
        private ?string $userCommand = null;

        /**
         * Initializes the command with its registry and metadata.
         *
         * @param CommandRegistry $registry The command registry instance.
         */
        public function __construct(CommandRegistry $registry)
        {
            parent::__construct(
                    $registry,
                    'help',
                    '[COMMAND]',
                    'View help for a given command, or all commands if no argument is given',
                    'create:project'
            );
        }

        /**
         * Executes the help operation.
         *
         * Calls the internal help method to display either all commands
         * or detailed information for a specific command.
         *
         * @return void
         */
        public function execute(): void
        {
            $this->help();
        }

        /**
         * Displays help information.
         *
         * - If no command is specified, lists all commands with descriptions.
         * - If a command is specified, shows detailed help for that command.
         *
         * @return void
         */
        private function help(): void
        {
            $text = Framework::NAME . ' v' . Framework::VERSION . ' Commandline Manual (Help)';
            $this->registry->addResult(Formatter::placeCenter($text, underline: true, sentenceWidth: 150) . PHP_EOL);
            $this->registry->addResult('For help type help COMMAND' . PHP_EOL);
            if ($this->userCommand === null) {
                $this->registry->addResult(Formatter::formatSentence('COMMAND', 'DESCRIPTION', separator: ' '));
                $commands = $this->registry->commandList();
                foreach ($commands as $index => $cmd) {
                    $message = Formatter::formatSentence(($index + 1) . '. ' . $cmd->commandName, $cmd->description);
                    $this->registry->addResult($message);
                }
            } else {
                $this->searchCommand();
            }
        }

        /**
         * Searches for a specific command and displays its help information.
         *
         * - If the command exists, shows its name, syntax, example, and description.
         * - If not found, attempts partial matches against command names and descriptions.
         * - Throws an exception if no matches are found.

         * @return void
         *
         * @throws \InvalidArgumentException If the command is not found.
         */
        private function searchCommand(): void
        {
            $command = $this->registry->getCommandByName($this->userCommand);
            if ($command !== null) {
                $this->registry->addResult(Formatter::formatSentence('COMMAND:', $command->commandName));
                $this->registry->addResult(Formatter::formatSentence('SYNTAX:', $command->syntax));
                $this->registry->addResult(Formatter::formatSentence('EXAMPLE:', $command->example));
                $this->registry->addResult('DESCRIPTION:' . PHP_EOL . $command->description);
                return;
            }

            $index = 1;
            $excluded = [];
            $commands = $this->registry->commandList();

            foreach ($commands as $cmd) {
                if (str_contains($cmd->commandName, $this->userCommand)) {
                    $excluded[$cmd->commandName] = 1;
                    $message = Formatter::formatSentence(($index++) . '. ' . $cmd->commandName, $cmd->description);
                    $this->registry->addResult($message);
                }
            }

            $commands2 = $this->registry->commandList();
            foreach ($commands2 as $cmd) {
                if (!isset($excluded[$cmd->commandName]) && str_contains(strtolower($cmd->description), $this->userCommand)) {
                    $message = Formatter::formatSentence(($index++) . '. ' . $cmd->commandName, $cmd->description);
                    $this->registry->addResult($message);
                }
            }

            if ($index === 1) {
                throw new \InvalidArgumentException('Command "' . $this->userCommand . '" not found.');
            }
        }

        /**
         * Parses command arguments.
         *
         * - If a command name is provided, stores it for detailed help.
         * - If no arguments are provided, defaults to listing all commands.
         *
         * @param string ...$args The command arguments (optional command name).
         *
         * @return string|null The command name or null if listing all commands.
         */
        public function parse(string ...$args): ?string
        {
            $this->userCommand = $args[0] ?? null;
            return $this->userCommand;
        }
    }

}
