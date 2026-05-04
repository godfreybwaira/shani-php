<?php

/**
 * Description of HelpCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\cli\commands {

    use features\cli\CommandContract;
    use features\cli\CommandRegistry;
    use features\cli\helpers\Formatter;
    use shani\launcher\Framework;

    final class HelpCommand extends CommandContract
    {

        private ?string $commandName = null;
        private readonly CommandRegistry $registry;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct('help', '[COMMAND]', 'View help for a given command, or all commands if no argument is given.', 'create:project');
            $this->registry = $registry;
        }

        public function execute(): void
        {
            $this->help();
        }

        private function help(): void
        {
            $index = 1;
            $width = 150;
            $text = Framework::NAME . ' v' . Framework::VERSION . ' Commandline Manual (Help)';
            echo PHP_EOL . Formatter::placeCenter($text, underline: true, sentenceWidth: $width) . PHP_EOL;
            if ($this->commandName === null) {
                echo Formatter::formatSentence('COMMAND', 'DESCRIPTION', sentenceWidth: $width, separator: ' ');
                $commands = $this->registry->getAllCommands();
                $commands->each(function (string $name, CommandContract $command) use (&$index, $width) {
                    echo Formatter::formatSentence(($index++) . '. ' . $name, $command->description, sentenceWidth: $width);
                });
            } else {
                $this->singleCommandHelp($width);
            }
            echo PHP_EOL;
        }

        private function singleCommandHelp(int $sentenceWidth): void
        {
            $command = $this->registry->getCommandByName($this->commandName);
            echo Formatter::formatSentence('COMMAND:', $command->name, sentenceWidth: $sentenceWidth);
            echo Formatter::formatSentence('SYNTAX:', $command->syntax, sentenceWidth: $sentenceWidth);
            echo Formatter::formatSentence('EXAMPLE:', $command->example, sentenceWidth: $sentenceWidth);
            echo 'DESCRIPTION:' . PHP_EOL . $command->description . PHP_EOL;
        }

        public function parse(string ...$args): CommandContract
        {
            if (count($args) > 1) {
                throw new \ArgumentCountError('Only one argument is allowed.');
            }
            $this->commandName = $args[0] ?? null;
            return $this;
        }
    }

}
