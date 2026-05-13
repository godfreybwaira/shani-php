<?php

/**
 * Description of ModuleName
 * @author goddy
 *
 * Created on: May 13, 2026 at 3:02:06 PM
 */

namespace features\console\helpers {

    use shani\utils\ShaniUtils;

    final class ModuleName
    {

        public readonly string $directoryName;
        public readonly string $className;
        public readonly string $pathName;
        public readonly string $originalValue;

        private function __construct(string $value)
        {
            $separator = '_';
            $tmpValue = str_replace('-', $separator, $value);
            $this->originalValue = $value;
            $this->directoryName = strtolower(ShaniUtils::splitByCase($tmpValue, $separator));
            $this->className = ShaniUtils::kebab2PascalCase($tmpValue, $separator);
            $this->pathName = str_replace($separator, '-', $this->directoryName);
        }

        public static function create($value): self
        {
            return new self($value);
        }
    }

}
