<?php

/**
 * Description of CommandRegistry
 * @author goddy
 *
 * Created on: May 3, 2026 at 7:41:04 PM
 */

namespace features\cli {

    use features\cli\helpers\Formatter;
    use features\ds\map\WritableMap;
    use shani\launcher\Framework;

    final class CommandRegistry
    {

        private readonly WritableMap $commands;

        public function __construct()
        {
            $this->commands = $this->registerAll();
        }

        public function run(string $commandName, string ...$args): void
        {
            $command = $this->getCommand($commandName);
            try {
                $command->parse(...$args)->execute();
            } catch (\InvalidArgumentException $e) {
                echo '[INFO] ' . $e->getMessage() . '. The syntax for this command is: ' . $command->syntax . PHP_EOL;
            } catch (\Throwable $t) {
                echo $t->getMessage() . PHP_EOL;
            }
        }

        public function help(?string $commandName): void
        {
            $index = 1;
            $width = 150;
            $text = Framework::NAME . ' v' . Framework::VERSION . ' Commandline Manual (Help)';
            echo PHP_EOL . Formatter::placeCenter($text, underline: true, sentenceWidth: $width) . PHP_EOL;
            if ($commandName === null) {
                echo Formatter::formatSentence('COMMAND', 'DESCRIPTION', sentenceWidth: $width, separator: ' ');
                $this->commands->each(function (string $name, CommandContract $command) use (&$index, $width) {
                    echo Formatter::formatSentence(($index++) . '. ' . $name, $command->description, sentenceWidth: $width);
                });
            } else {
                $this->singleCommandHelp($commandName, $width);
            }
            echo PHP_EOL;
        }

        private function singleCommandHelp(string $commandName, int $sentenceWidth): void
        {
            $command = $this->getCommand($commandName);
            echo Formatter::formatSentence('COMMAND:', $command->name, sentenceWidth: $sentenceWidth);
            echo Formatter::formatSentence('SYNTAX:', $command->syntax, sentenceWidth: $sentenceWidth);
            echo Formatter::formatSentence('EXAMPLE:', $command->example, sentenceWidth: $sentenceWidth);
            echo 'DESCRIPTION:' . PHP_EOL . $command->description . PHP_EOL;
        }

        private function registerAll(): WritableMap
        {
            $cmd1 = new commands\CreateProjectCommand();
            $cmd2 = new commands\CreateModuleCommand();
            return new WritableMap([
                $cmd1->name => $cmd1,
                $cmd2->name => $cmd2
            ]);
        }

        private function getCommand(string $commandName): CommandContract
        {
            $command = $this->commands->getOne($commandName);
            if ($command !== null) {
                return $command;
            }
            throw new \InvalidArgumentException('Command "' . $commandName . '" not found.');
        }
    }

}
