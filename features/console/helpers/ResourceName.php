<?php

/**
 * Description of ResourceName
 * @author goddy
 *
 * Created on: May 11, 2026 at 5:25:41 PM
 */

namespace features\console\helpers {

    use features\console\CommandContract;
    use shani\launcher\ShaniUtils;

    final class ResourceName implements \Stringable
    {

        public readonly string $name;
        public readonly string $suffix;
        public readonly string $value;

        public function __construct(string $name, string $suffix)
        {
            $this->suffix = $suffix;
            $this->name = ShaniUtils::trimSuffix($name, $suffix);
            $this->value = $this->name . $suffix;
            CommandContract::validateIdentifier($this->value);
        }

        #[\Override]
        public function __toString(): string
        {
            return $this->value;
        }
    }

}
