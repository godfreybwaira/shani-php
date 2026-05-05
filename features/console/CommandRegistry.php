<?php

/**
 * Description of CommandRegistry
 * @author goddy
 *
 * Created on: May 3, 2026 at 7:41:04 PM
 */

namespace features\console {

    use features\ds\map\ReadableMap;
    use features\ds\map\WritableMap;

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

        /**
         * Initialize the registry and register all commands.
         */
        public function __construct()
        {
            $this->commands = $this->registerAll();
        }

        /**
         * Generator that yields all available command instances.
         *
         * @return \Generator<CommandContract>
         */
        private function commandList(): \Generator
        {
            yield new commands\HelpCommand($this);
            /////////////////////////////////////////
            yield new commands\CreateProjectCommand();
            yield new commands\CreateModuleCommand();
            yield new commands\CreateControllerCommand();
            yield new commands\CreateVHostCommand();
            yield new commands\CreateAliasCommand();
            yield new commands\CreateEntityCommand();
            /////////////////////////////////////////
            yield new commands\ListProjectCommand();
            yield new commands\ListProjectModulesCommand();
            yield new commands\ListProjectControllersCommand();
            yield new commands\ListHostAliasCommand();
            yield new commands\ListDtoCommand();
            yield new commands\ListEntityCommand();
            yield new commands\ListServiceCommand();
            yield new commands\ListVhostCommand();
            /////////////////////////////////////////
            yield new commands\DeleteAliasCommand();
            yield new commands\DeleteVhostCommand();
            /////////////////////////////////////////
            yield new commands\RenameAliasCommand();
            yield new commands\RenameVhostCommand();
            /////////////////////////////////////////
            yield new commands\LocateProjectCommand();
            yield new commands\LocateVhostCommand();
            yield new commands\LocateAliasCommand();
            yield new commands\LocateModuleCommand();
            yield new commands\LocateControllerCommand();
            yield new commands\LocateServiceCommand();
            yield new commands\LocateEntityCommand();
            yield new commands\LocateDtoCommand();
        }

        /**
         * Run a command by name with arguments.
         *
         * @param string $commandName The name of the command to execute.
         * @param string ...$args     Arguments passed to the command.
         *
         * @return void
         *
         * @throws \InvalidArgumentException If the command is not found or arguments are invalid.
         * @throws \Throwable For any other runtime errors during execution.
         */
        public function run(string $commandName, string ...$args): void
        {
            $command = $this->getCommandByName($commandName);
            try {
                $verbose = in_array('--verbose', $args);
                $noColor = in_array('--no-color', $args);
                $command->setOptions(new CommandOptions($verbose, $noColor));
                $command->parse(...$args)->execute();
            } catch (\InvalidArgumentException $e) {
                echo '[INFO] ' . $e->getMessage() . '. The syntax for this command is: ' . $command->syntax . PHP_EOL;
            } catch (\Throwable $t) {
                echo $t->getMessage() . PHP_EOL;
            }
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
    }

}