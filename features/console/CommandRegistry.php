<?php

/**
 * Description of CommandRegistry
 * @author goddy
 *
 * Created on: May 3, 2026 at 7:41:04 PM
 */

namespace features\console {

    use features\console\printer\ConsoleIO;
    use features\console\printer\PrintedText;
    use features\ds\map\ReadableMap;
    use features\ds\map\WritableMap;
    use shani\launcher\Framework;

    /**
     * Class CommandRegistry
     *
     * Maintains a registry of all available CLI commands.
     * Responsible for registering, retrieving, and executing commands
     * by name with their arguments. Provides access to the full command map.
     *
     */
    final class CommandRegistry
    {

        /** Map of command names to their CommandContract instances. */
        private readonly WritableMap $commands;

        /** User command options */
        private readonly CommandOptions $options;

        /** Command arguments */
        private readonly array $arguments;

        /** Command name to execute */
        private readonly string $commandName;

        /**
         * Command results
         * @var array
         */
        private array $commandResults = [];

        /** Whether to display a banner */
        public bool $showBanner = false;

        /**
         * Initialize the registry and register all commands.
         *
         * @param string $commandName A command name to execute
         *
         * @param array $arguments Command arguments
         */
        public function __construct(string $commandName, array $arguments)
        {
            $this->commandName = $commandName;
            $this->arguments = $arguments;
            $quiet = in_array('--quiet', $arguments);
            $noColor = in_array('--no-color', $arguments);
            $this->options = new CommandOptions($quiet, $noColor);
            $this->commands = $this->registerAll();
        }

        /**
         * Generator that yields all available command instances.
         *
         * @return \Generator<CommandContract>
         */
        private function commandList(): \Generator
        {
            // General
            yield new commands\HelpCommand($this);
        }

        /**
         * Run a command by name.
         */
        public function run(): void
        {
            $command = $this->getCommandByName($this->commandName);
            $command->parse(...$this->arguments)->execute();
            $this->showResults();
        }

        /**
         * Register all commands into a writable map.
         *
         * @return WritableMap Map of command names to command instances.
         */
        private function registerAll(): WritableMap
        {
            $map = new WritableMap();
            $commands = $this->commandList();
            foreach ($commands as $command) {
                $map->addOne($command->name, $command);
            }
            return $map;
        }

        /**
         * Retrieve a command by its name.
         *
         * @param string $commandName The name of the command.
         * @return CommandContract The command instance.
         *
         * @throws \InvalidArgumentException If the command is not found.
         */
        public function getCommandByName(string $commandName): CommandContract
        {
            $command = $this->commands->getOne($commandName);
            if ($command !== null) {
                return $command;
            }
            throw new \InvalidArgumentException('Command "' . $commandName . '" not found.');
        }

        /**
         * Get all registered commands.
         *
         * @return ReadableMap Map of all commands.
         */
        public function getAllCommands(): ReadableMap
        {
            return $this->commands;
        }

        private static function printBanner(): void
        {
            $banner = fopen(CommandContract::ASSETS . '/banner.txt', 'rb');
            ConsoleIO::output(PHP_EOL);
            while (($line = fgets($banner)) !== false) {
                ConsoleIO::output(PrintedText::info($line)->coloredText, false);
            }
            ConsoleIO::output(PrintedText::bold('v' . Framework::VERSION) . PHP_EOL . PHP_EOL);
            fclose($banner);
        }

        /**
         * Add command result to display when command finishes execution.
         * @param PrintedText|string $message Result Message
         * @return self
         */
        public function addResult(PrintedText|string $message): self
        {
            if (!$this->options->quiet) {
                $this->commandResults[] = $message instanceof PrintedText ? $message : PrintedText::plain($message);
            }
            return $this;
        }

        private function showResults(): void
        {
            if ($this->options->quiet) {
                return;
            }
            if ($this->showBanner) {
                self::printBanner();
            }
            if ($this->options->noColor) {
                foreach ($this->commandResults as $message) {
                    ConsoleIO::output($message->plainText);
                }
            } else {
                foreach ($this->commandResults as $message) {
                    ConsoleIO::output($message->coloredText);
                }
            }
            ConsoleIO::output('Done' . PHP_EOL);
        }
    }

}