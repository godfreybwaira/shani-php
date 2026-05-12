<?php

/**
 * Description of CommandContract
 * @author goddy
 *
 * Created on: May 3, 2026 at 7:59:17 PM
 */

namespace features\console {

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
        public readonly string $name;

        /** The full syntax string including arguments. */
        public readonly string $syntax;

        /** A human-readable description of the command. */
        public readonly string $description;

        /** An example usage string for documentation. */
        public readonly string $example;
        protected readonly \Closure $validIdentifier;
        protected readonly \Closure $validHostName;

        /** Separator used internally for command parsing. */
        protected const SEPARATOR = '@';

        /** Path to console assets directory. */
        public const ASSETS = Framework::DIR_FEATURES . '/console/assets';

        /**
         * Construct a new command contract.
         *
         * @param string      $name        Command name.
         * @param string|null $syntax      Command syntax (arguments).
         * @param string      $description Command description.
         * @param string|null $example     Example usage.
         */
        protected function __construct(string $name, ?string $syntax, string $description, ?string $example)
        {
            $this->name = $name;
            $this->description = $description;
            $this->syntax = trim($name . ' ' . $syntax);
            $this->example = trim($name . ' ' . $example);
            $this->validHostName = fn(string $s) => self::validateHostName($s, false);
            $this->validIdentifier = fn(string $s) => self::validateIdentifier($s, false);
        }

        /**
         * Validate that an identifier is alphanumeric with underscores.
         *
         * @param string $value Identifier to validate.
         * @param bool $throw Whether to throw an exception when validation failed.
         *
         * @return bool True when validation passes, false otherwise
         * @throws \InvalidArgumentException If invalid.
         */
        public static final function validateIdentifier(string $value, bool $throw = true): bool
        {
            if (preg_match('/^[a-zA-Z]+([0-9a-zA-Z_]+)*$/', $value) === 1) {
                return true;
            }
            if ($throw) {
                throw new \InvalidArgumentException('Invalid identifier "' . $value . '"');
            }
            return false;
        }

        /**
         * Validate that a hostname is alphanumeric with dots, hyphens, or underscores.
         *
         * @param string $value Hostname to validate.
         * @param bool $throw Whether to throw an exception when validation failed.
         *
         * @return bool True when validation passes, false otherwise
         * @throws \InvalidArgumentException If invalid.
         */
        public static final function validateHostName(string $value, bool $throw = true): bool
        {
            if (preg_match('/^[a-zA-Z]+([0-9a-zA-Z_.-]+)*$/', $value) === 1) {
                return true;
            }
            if ($throw) {
                throw new \InvalidArgumentException('Invalid hostname "' . $value . '"');
            }
            return false;
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
                'command_name' => $this->name,
                'syntax' => $this->syntax,
                'description' => $this->description,
                'example' => $this->example,
            ];
        }

        /**
         * Inspect and parse command arguments.
         *
         * @param string ...$args Arguments expected by the command.
         * @return CommandContract Returns the command instance for chaining.
         * @throws \InvalidArgumentException If arguments are invalid.
         */
        public abstract function parse(string ...$args): CommandContract;

        /**
         * Execute the command logic.
         *
         * @return void
         */
        public abstract function execute(): void;
    }

}
