<?php

/**
 * Represents an email address with optional display name.
 *
 * Provides validation, parsing of domain and username,
 * and a string representation suitable for email headers.
 *
 * @author goddy
 * @created Jun 1, 2026 at 11:11:58 PM
 */

namespace features\smtp\values {

    /**
     * Immutable value object for an email address.
     */
    final class Email implements \Stringable
    {

        /**
         * The full email address string.
         *
         * @var string
         */
        public readonly string $value;

        /**
         * The domain part of the email (after '@').
         *
         * @var string
         */
        public readonly string $domain;

        /**
         * The username part of the email (before '@').
         *
         * @var string
         */
        public readonly string $username;

        /**
         * Optional display name associated with the email.
         *
         * @var string|null
         */
        public readonly ?string $name;

        /**
         * Constructs a new Email value object.
         *
         * @param string      $value The email address to validate and store.
         * @param string|null $name  Optional display name for the email.
         *
         * @throws \InvalidArgumentException If the email address is invalid.
         */
        public function __construct(string $value, ?string $name = null)
        {
            if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
                throw new \InvalidArgumentException('Invalid email address ' . $value);
            }
            $this->value = $value;
            $this->name = $name;
            $atpos = strpos($value, '@');
            $this->domain = substr($value, $atpos + 1);
            $this->username = substr($value, 0, $atpos);
        }

        /**
         * Returns the email as a formatted string.
         *
         * If a display name is provided, the format will be:"Name" <email@example.com>
         *
         * Otherwise, it will be:<email@example.com>
         *
         * @return string
         */
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
