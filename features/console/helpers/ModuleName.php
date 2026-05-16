<?php

namespace features\console\helpers {

    use shani\utils\ShaniUtils;

    /**
     * Represents a normalized module name with multiple derived formats.
     *
     * Converts a raw module name string into consistent representations
     * for directory naming, class naming, and path usage.
     *
     * @author goddy
     * @created May 13, 2026 at 3:02:06 PM
     */
    final class ModuleName
    {

        /**
         * Directory-friendly version of the module name.
         * Lowercased and separated by underscores.
         *
         * @var string
         */
        public readonly string $directoryName;

        /**
         * Class-friendly version of the module name.
         * Converted to PascalCase.
         *
         * @var string
         */
        public readonly string $className;

        /**
         * Path-friendly version of the module name.
         * Uses kebab-case (hyphen-separated).
         *
         * @var string
         */
        public readonly string $pathName;

        /**
         * The original raw value provided by the user.
         *
         * @var string
         */
        public readonly string $originalValue;

        /**
         * Private constructor to enforce creation via factory method.
         *
         * @param string $value The raw module name input.
         *
         * @throws \InvalidArgumentException If the class name is invalid.
         */
        private function __construct(string $value)
        {
            $separator = '_';
            $tmpValue = str_replace('-', $separator, $value);

            $this->originalValue = $value;
            $this->directoryName = strtolower(ShaniUtils::splitByCase($tmpValue, $separator));
            $this->className = ShaniUtils::kebab2PascalCase($tmpValue, $separator);
            $this->pathName = str_replace($separator, '-', $this->directoryName);

            ResourceName::validate($this->className);
        }

        /**
         * Factory method to create a ModuleName instance.
         *
         * @param string $value The raw module name input.
         *
         * @return ModuleName A new ModuleName instance.
         */
        public static function create(string $value): ModuleName
        {
            return new self($value);
        }
    }

}
