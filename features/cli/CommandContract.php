<?php

/**
 * Description of CommandInterface
 * @author goddy
 *
 * Created on: May 3, 2026 at 7:59:17 PM
 */

namespace features\cli {

    use shani\launcher\Framework;

    abstract class CommandContract implements \JsonSerializable
    {

        public readonly string $name;
        public readonly string $syntax;
        public readonly string $description;
        public readonly string $example;

        protected const SEPARATOR = '@';
        public const ASSETS = Framework::DIR_FEATURES . '/cli/assets';

        protected function __construct(string $name, ?string $syntax, string $description, ?string $example)
        {
            $this->name = $name;
            $this->description = $description;
            $this->syntax = trim($name . ' ' . $syntax);
            $this->example = trim($name . ' ' . $example);
        }

        public final function validateIdentifier(string $value): void
        {
            if (preg_match('/^[a-zA-Z]+([0-9a-zA-Z_]+)*$/', $value) !== 1) {
                throw new \InvalidArgumentException('Invalid identifier "' . $value . '"');
            }
        }

        public final function validateHostName(string $value): void
        {
            if (preg_match('/^[a-zA-Z]+([0-9a-zA-Z_.-]+)*$/', $value) !== 1) {
                throw new \InvalidArgumentException('Invalid hostname "' . $value . '"');
            }
        }

        #[\Override]
        public final function jsonSerialize(): array
        {
            return[
                'command_name' => $this->name,
                'syntax' => $this->syntax,
                'description' => $this->description,
                'example' => $this->example
            ];
        }

        /**
         * Inspect command arguments to see if they comply with the command requirements.
         * If not then exception is thrown, store to use them later.
         * @param string $args List of argument that a command is expecting.
         * @return CommandContract Command contract object for chaining
         * @throws \InvalidArgumentException
         */
        public abstract function parse(string ...$args): CommandContract;

        /**
         * Execute a given command.
         * @return void
         */
        public abstract function execute(): void;
    }

}
