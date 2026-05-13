<?php

/**
 * Description of CommandRegistry
 * @author goddy
 *
 * Created on: May 3, 2026 at 7:41:04 PM
 */

namespace features\console {

    use features\console\printer\ConsoleColor;
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
        public readonly CommandOptions $options;

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
        public bool $showBanner = true;

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
            $quiet = in_array('--quiet', $arguments) || str_starts_with($commandName, 'locate:');
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

            // Project
            yield new commands\project\CreateProjectCommand($this);
            yield new commands\project\ListProjectCommand($this);
            yield new commands\project\LocateProjectCommand($this);
            yield new commands\project\DeleteProjectCommand($this);
//
            //Version
//            yield new commands\version\CreateVersionCommand($this);
//            yield new commands\version\DeleteVersionCommand($this);
//            yield new commands\version\ListVersionCommand($this);
//            yield new commands\version\LocateVersionCommand($this);
//
//            // Module
//            yield new commands\module\CreateModuleCommand($this);
//            yield new commands\module\LocateModuleCommand($this);
//            yield new commands\module\ListModulesCommand($this);
//
//            // Controller
//            yield new commands\controller\CreateControllerCommand($this);
//            yield new commands\controller\LocateControllerCommand($this);
//            yield new commands\controller\ListControllersCommand($this);
//
//            // VHost
//            yield new commands\vhost\ListVhostCommand($this);
//            yield new commands\vhost\RenameVhostCommand($this);
//            yield new commands\vhost\LocateVhostCommand($this);
//
//            // Alias
//            yield new commands\alias\CreateAliasCommand($this);
//            yield new commands\alias\ListHostAliasCommand($this);
//            yield new commands\alias\DeleteAliasCommand($this);
//            yield new commands\alias\RenameAliasCommand($this);
//            yield new commands\alias\LocateAliasCommand($this);
//
//            // Entity
//            yield new commands\entity\CreateEntityCommand($this);
//            yield new commands\entity\ListEntityCommand($this);
//            yield new commands\entity\LocateEntityCommand($this);
//
//            // DTO
//            yield new commands\dto\ListDtoCommand($this);
//            yield new commands\dto\LocateDtoCommand($this);
//
//            // Service
//            yield new commands\service\ListServiceCommand($this);
//            yield new commands\service\LocateServiceCommand($this);
        }

        /**
         * Run a command by name.
         */
        public function run(): void
        {
            $command = $this->getCommandByName($this->commandName);
            $parameters = $command->parse(...$this->arguments);
            $info = PrintedText::info('[ INFO ] ');
            $infoMsg = $this->options->noColor ? $info->plainText : $info->coloredText;
            $message = PrintedText::bold($this->commandName . ' ' . $parameters)->coloredText;
            $this->addResult(PrintedText::plain($infoMsg . 'Executing command ' . $message . PHP_EOL));
            $command->execute();
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
                $map->addOne($command->commandName, $command);
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

        public static function printBanner(ConsoleColor $color = ConsoleColor::GREEN): void
        {
            $banner = fopen(CommandContract::ASSETS . '/banner.txt', 'rb');
            ConsoleIO::output(PHP_EOL);
            while (($line = fgets($banner)) !== false) {
                ConsoleIO::output(PrintedText::color(' ' . $line, $color)->coloredText, false);
            }
            ConsoleIO::output(PrintedText::bold('v' . Framework::VERSION) . PHP_EOL . PHP_EOL);
            fclose($banner);
        }

        /**
         * Add command result to display when command finishes execution.
         * @param PrintedText|string|null $message Result Message
         * @return self
         */
        public function addResult(PrintedText|string|null $message): self
        {
            if (!$this->options->quiet && !empty($message)) {
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
            ConsoleIO::output(PHP_EOL . 'Done' . PHP_EOL);
        }
    }

}