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

        private function registerAll(): WritableMap
        {
            $cmd0 = new commands\HelpCommand($this);
            /////////////////////////////////////////
            $cmd1 = new commands\CreateProjectCommand();
            $cmd2 = new commands\CreateModuleCommand();
            $cmd3 = new commands\CreateControllerCommand();
            $cmd4 = new commands\CreateVHostCommand();
            $cmd5 = new commands\CreateAliasCommand();
            $cmd22 = new commands\CreateEntityCommand();
            /////////////////////////////////////////
            $cmd6 = new commands\ListProjectCommand();
            $cmd7 = new commands\ListProjectModulesCommand();
            $cmd8 = new commands\ListProjectControllersCommand();
            $cmd9 = new commands\ListHostAliasCommand();
            /////////////////////////////////////////
            $cmd10 = new commands\DeleteAliasCommand();
            $cmd11 = new commands\DeleteVhostCommand();
            /////////////////////////////////////////
            $cmd12 = new commands\RenameAliasCommand();
            $cmd13 = new commands\RenameVhostCommand();
            /////////////////////////////////////////
            $cmd14 = new commands\LocateProjectCommand();
            $cmd15 = new commands\LocateVhostCommand();
            $cmd16 = new commands\LocateAliasCommand();
            $cmd17 = new commands\LocateModuleCommand();
            $cmd18 = new commands\LocateControllerCommand();
            $cmd19 = new commands\LocateServiceCommand();
            $cmd20 = new commands\LocateEntityCommand();
            $cmd21 = new commands\LocateDtoCommand();
            /////////////////////////////////////////
            return new WritableMap([
                $cmd0->name => $cmd0,
                $cmd1->name => $cmd1,
                $cmd2->name => $cmd2,
                $cmd3->name => $cmd3,
                $cmd4->name => $cmd4,
                $cmd5->name => $cmd5,
                $cmd6->name => $cmd6,
                $cmd7->name => $cmd7,
                $cmd8->name => $cmd8,
                $cmd9->name => $cmd9,
                $cmd10->name => $cmd10,
                $cmd11->name => $cmd11,
                $cmd12->name => $cmd12,
                $cmd13->name => $cmd13,
                $cmd14->name => $cmd14,
                $cmd15->name => $cmd15,
                $cmd16->name => $cmd16,
                $cmd17->name => $cmd17,
                $cmd18->name => $cmd18,
                $cmd19->name => $cmd19,
                $cmd20->name => $cmd20,
                $cmd21->name => $cmd21,
                $cmd22->name => $cmd22,
            ]);
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