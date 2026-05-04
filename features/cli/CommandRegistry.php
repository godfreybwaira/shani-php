<?php

/**
 * Description of CommandRegistry
 * @author goddy
 *
 * Created on: May 3, 2026 at 7:41:04 PM
 */

namespace features\cli {

    use features\ds\map\ReadableMap;
    use features\ds\map\WritableMap;

    final class CommandRegistry
    {

        private readonly WritableMap $commands;

        public function __construct()
        {
            $this->commands = $this->registerAll();
        }

        public function run(string $commandName, string ...$args): void
        {
            $command = $this->getCommandByName($commandName);
            try {
                $command->parse(...$args)->execute();
            } catch (\InvalidArgumentException $e) {
                echo '[INFO] ' . $e->getMessage() . '. The syntax for this command is: ' . $command->syntax . PHP_EOL;
            } catch (\Throwable $t) {
                echo $t->getMessage() . PHP_EOL;
            }
        }

        /**
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

        private function registerAll(): WritableMap
        {
            $map = new WritableMap();
            $commands = $this->commandList();
            foreach ($commands as $command) {
                $map->addOne($command->name, $command);
            }
            return $map;
        }

        public function getCommandByName(string $commandName): CommandContract
        {
            $command = $this->commands->getOne($commandName);
            if ($command !== null) {
                return $command;
            }
            throw new \InvalidArgumentException('Command "' . $commandName . '" not found.');
        }

        public function getAllCommands(): ReadableMap
        {
            return $this->commands;
        }
    }

}