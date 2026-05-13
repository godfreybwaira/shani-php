<?php

/**
 * Description of ResourceName
 * @author goddy
 *
 * Created on: May 11, 2026 at 5:25:41 PM
 */

namespace features\console\helpers {

    use shani\launcher\ShaniUtils;

    final class ResourceName implements \Stringable
    {

        public readonly string $name;
        public readonly string $suffix;
        public readonly string $value;

        private function __construct(string $name, string $suffix)
        {
            $this->suffix = $suffix;
            $this->name = ShaniUtils::trimSuffix($name, $suffix);
            $this->value = $this->name . $suffix;
            self::validate($this->value);
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
        public static final function validate(string $value, bool $throw = true): bool
        {
            if (preg_match('/^[a-zA-Z]+([0-9a-zA-Z_]+)*$/', $value) === 1) {
                return true;
            }
            if ($throw) {
                throw new \InvalidArgumentException('Invalid identifier "' . $value . '"');
            }
            return false;
        }

        #[\Override]
        public function __toString(): string
        {
            return $this->value;
        }

        public static function create(string $name, string $suffix = ''): self
        {
            return new self($name, $suffix);
        }
    }

}
