<?php

/**
 * Description of HostName
 * @author goddy
 *
 * Created on: May 11, 2026 at 5:25:41 PM
 */

namespace features\console\helpers {

    final class HostName
    {

        public static function create(string $value): string
        {
            self::validate($value);
            return $value;
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
        public static final function validate(string $value, bool $throw = true): bool
        {
            if (preg_match('/^[a-zA-Z]+([0-9a-zA-Z_.-]+)*$/', $value) === 1) {
                return true;
            }
            if ($throw) {
                throw new \InvalidArgumentException('Invalid hostname "' . $value . '"');
            }
            return false;
        }
    }

}
