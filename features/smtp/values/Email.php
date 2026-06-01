<?php

/**
 * Description of Email
 * @author goddy
 *
 * Created on: Jun 1, 2026 at 11:11:58 PM
 */

namespace features\smtp\values {

    final class Email implements \Stringable
    {

        public readonly string $value;
        public readonly ?string $name;

        public function __construct(string $value, ?string $name = null)
        {
            if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                throw new \InvalidArgumentException('Invalid email address ' . $value);
            }
            $this->value = $value;
            $this->name = $name;
        }

        #[\Override]
        public function __toString(): string
        {
            if (!empty($this->name)) {
                return '"' . $this->name . '" <' . $this->value . '>';
            }
            return '<' . $this->value . '>';
        }
    }

}
