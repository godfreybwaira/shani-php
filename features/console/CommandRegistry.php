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
            // General
            yield new commands\HelpCommand($this);

            // Project
            yield new commands\project\CreateProjectCommand();
            yield new commands\project\ListProjectCommand();
            yield new commands\project\LocateProjectCommand();
            yield new commands\project\DeleteProjectCommand();

            //Version
            yield new commands\version\CreateVersionCommand();
            yield new commands\version\DeleteVersionCommand();
            yield new commands\version\ListVersionCommand();
            yield new commands\version\LocateVersionCommand();

            // Module
            yield new commands\module\CreateModuleCommand();
            yield new commands\module\LocateModuleCommand();
            yield new commands\module\ListModulesCommand();

            // Controller
            yield new commands\controller\CreateControllerCommand();
            yield new commands\controller\LocateControllerCommand();
            yield new commands\controller\ListControllersCommand();

            // VHost
            yield new commands\vhost\ListVhostCommand();
            yield new commands\vhost\RenameVhostCommand();
            yield new commands\vhost\LocateVhostCommand();

            // Alias
            yield new commands\alias\CreateAliasCommand();
            yield new commands\alias\ListHostAliasCommand();
            yield new commands\alias\DeleteAliasCommand();
            yield new commands\alias\RenameAliasCommand();
            yield new commands\alias\LocateAliasCommand();

            // Entity
            yield new commands\entity\CreateEntityCommand();
            yield new commands\entity\ListEntityCommand();
            yield new commands\entity\LocateEntityCommand();

            // DTO
            yield new commands\dto\ListDtoCommand();
            yield new commands\dto\LocateDtoCommand();

            // Service
            yield new commands\service\ListServiceCommand();
            yield new commands\service\LocateServiceCommand();
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