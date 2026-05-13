<?php

/**
 * Description of CommandContract
 * @author goddy
 *
 * Created on: May 3, 2026 at 7:59:17 PM
 */

namespace features\console {

    use features\console\helpers\HostName;
    use features\console\helpers\ResourceName;
    use shani\launcher\Framework;

    /**
     * Abstract Class CommandContract
     *
     * Defines the base contract for all CLI commands in the framework.
     * Provides common metadata (name, syntax, description, example) and
     * enforces validation, option handling, and JSON serialization.
     *
     * Concrete command classes must implement `parse()` and `execute()`.
     *
     */
    abstract class CommandContract implements \JsonSerializable
    {

        /** The command name (e.g., "create:project"). */
        public readonly string $commandName;

        /** The full syntax string including arguments. */
        public readonly string $syntax;

        /** A human-readable description of the command. */
        public readonly string $description;

        /** An example usage string for documentation. */
        public readonly string $example;
        protected readonly \Closure $validIdentifier;
        protected readonly \Closure $validHostName;

        /** Command registry object */
        protected readonly CommandRegistry $registry;

        /** Separator used internally for command parsing. */
        protected const SEPARATOR = '@';

        /** Path to console assets directory. */
        public const ASSETS = Framework::DIR_FEATURES . '/console/assets';

        /**
         * Construct a new command contract.
         *
         * @param CommandRegistry $registry Command registry object
         * @param string      $name        Command name.
         * @param string|null $syntax      Command syntax (arguments).
         * @param string      $description Command description.
         * @param string|null $example     Example usage.
         */
        protected function __construct(CommandRegistry $registry, string $name, ?string $syntax, string $description, ?string $example)
        {
            $this->registry = $registry;
            $this->commandName = $name;
            $this->description = $description;
            $this->syntax = trim($name . ' ' . $syntax);
            $this->example = trim($name . ' ' . $example);
            $this->validHostName = fn(string $s) => HostName::validate($s, false);
            $this->validIdentifier = fn(string $s) => ResourceName::validate($s, false);
        }

        /**
         * Serialize command metadata to JSON.
         *
         * @return array<string,string> Command metadata.
         */
        #[\Override]
        public final function jsonSerialize(): array
        {
            return [
                'command_name' => $this->commandName,
                'syntax' => $this->syntax,
                'description' => $this->description,
                'example' => $this->example,
            ];
        }

        /**
         * Inspect and parse command arguments.
         *
         * @param string ...$args   Arguments expected by the command.
         * @return string|null      Command being executed
         * @throws \InvalidArgumentException If arguments are invalid.
         */
        public abstract function parse(string ...$args): string;

        /**
         * Execute the command logic.
         *
         * @return void
         */
        public abstract function execute(): void;
    }

}
