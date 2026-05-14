<?php

/**
 * Description of HelpCommand
 * @author goddy
 *
 * Created on: May 3, 2026 at 8:59:28 PM
 */

namespace features\console\commands {

    use features\console\CommandContract;
    use features\console\CommandRegistry;
    use features\console\helpers\Formatter;
    use shani\launcher\Framework;

    final class HelpCommand extends CommandContract
    {

        private ?string $userCommand = null;

        public function __construct(CommandRegistry $registry)
        {
            parent::__construct($registry, 'help', '[COMMAND]', 'View help for a given command, or all commands if no argument is given', 'help create:project');
        }

        public function execute(): void
        {
            $this->help();
        }

        private function help(): void
        {
            $width = 150;
            $text = Framework::NAME . ' v' . Framework::VERSION . ' Commandline Manual (Help)';
            $this->registry->addResult(Formatter::placeCenter($text, underline: true, sentenceWidth: $width) . PHP_EOL);
            if ($this->userCommand === null) {
                $this->registry->addResult(Formatter::formatSentence('COMMAND', 'DESCRIPTION', sentenceWidth: $width, separator: ' '));
                $commands = $this->registry->commandList();
                foreach ($commands as $index => $cmd) {
                    $this->registry->addResult(Formatter::formatSentence($index . '. ' . $cmd->commandName, $cmd->description, sentenceWidth: $width));
                }
            } else {
                $this->searchCommand($width);
            }
        }

        private function searchCommand(int $sentenceWidth): void
        {
            $command = $this->registry->getCommandByName($this->userCommand);
            if ($command !== null) {
                $this->registry->addResult(Formatter::formatSentence('COMMAND:', $command->commandName, sentenceWidth: $sentenceWidth));
                $this->registry->addResult(Formatter::formatSentence('SYNTAX:', $command->syntax, sentenceWidth: $sentenceWidth));
                $this->registry->addResult(Formatter::formatSentence('EXAMPLE:', $command->example, sentenceWidth: $sentenceWidth));
                $this->registry->addResult('DESCRIPTION:' . PHP_EOL . $command->description);
                return;
            }
            $index = 1;
            $excluded = [];
            $commands = $this->registry->commandList();
            foreach ($commands as $cmd) {
                if (str_contains($cmd->commandName, $this->userCommand)) {
                    $excluded[$cmd->commandName] = 1;
                    $this->registry->addResult(Formatter::formatSentence(($index++) . '. ' . $cmd->commandName, $cmd->description, sentenceWidth: $sentenceWidth));
                }
            }
            $commands2 = $this->registry->commandList();
            foreach ($commands2 as $cmd) {
                if (!isset($excluded[$cmd->commandName]) && str_contains(strtolower($cmd->description), $this->userCommand)) {
                    $this->registry->addResult(Formatter::formatSentence(($index++) . '. ' . $cmd->commandName, $cmd->description, sentenceWidth: $sentenceWidth));
                }
            }
            if ($index === 1) {
                throw new \InvalidArgumentException('Command "' . $this->userCommand . '" not found.');
            }
        }

        public function parse(string ...$args): ?string
        {
            $this->userCommand = $args[0] ?? null;
            return $this->userCommand ?? '';
        }
    }

}
