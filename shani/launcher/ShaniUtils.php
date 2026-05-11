<?php

namespace shani\launcher {

    final class ShaniUtils
    {

        public static function define(string $root): void
        {
            /**
             * Server root directory
             */
            define('SHANI_SERVER_ROOT', $root);
            /**
             * Current timestamp according to RFC3339
             */
            define('SHANI_CURRENT_TIMESTAMP', date(DATE_RFC3339));
        }

        /**
         * Removes a specific suffix from the end of a string (case-insensitive).
         *
         * @param string $value  The string to trim.
         * @param string $suffix The suffix to look for and remove.
         *
         * @return string The string without the suffix if it was found; otherwise, the original string.
         */
        public static function trimSuffix(string $value, string $suffix): string
        {
            if ($suffix !== '' && str_ends_with(strtolower($value), strtolower($suffix))) {
                return substr($value, 0, -strlen($suffix)); // ensuring we only remove it from the end.
            }
            return $value;
        }

        /**
         * Converts a string with separators to camelCase (e.g., "my-string" to "myString").
         *
         * @param string $str       The input string to convert.
         * @param string $separator The character separating words. Defaults to '-'.
         *
         * @return string The resulting camelCase string.
         */
        public static function kebab2camelCase(string $str, string $separator = '-'): string
        {
            return lcfirst(self::kebab2PascalCase($str, $separator));
        }

        /**
         * Converts a string with separators to PascalCase (e.g., "my-string" to "MyString").
         *
         * @param string $str       The input string to convert.
         * @param string $separator The character separating words. Defaults to '-'.
         *
         * @return string The resulting PascalCase string.
         */
        public static function kebab2PascalCase(string $str, string $separator = '-'): string
        {
            $normalized = ucwords(strtolower($str), $separator);
            return str_replace($separator, '', $normalized);
        }

        /**
         * Converts camelCase or PascalCase strings into space-separated lowercase words,
         * with special handling for acronyms like "NASA".
         *
         * @param string $str The string to convert (e.g., "NASAComponentsController").
         * @param string $separator The character separating words. Defaults to ' '.
         *
         * @return string The converted string (e.g., "NASA Components Controller").
         */
        public static function camel2Words(string $str, string $separator = ' '): string
        {
            // Insert space before any uppercase letter that follows a lowercase or digit
            $str2 = preg_replace('/(?<=[a-z0-9])([A-Z])/', ' $1', $str);
            // Handle acronyms: split when multiple uppercase letters are followed by lowercase
            return preg_replace('/([A-Z])([A-Z][a-z])/', '$1' . $separator . '$2', $str2);
        }
    }

}