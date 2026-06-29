<?php

namespace shani\utils {

    final class ShaniUtils
    {

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
         * Converts a camelCase or PascalCase string to kebab-case.
         * Example: "NASAComponents" -> "nasa-components"
         *
         * @param string $str       The string to convert.
         * @param string $separator The character used to separate words. Defaults to '-'.
         *
         * @return string   The lower-case-kebab-string
         */
        public static function camelCase2kebab(string $str, string $separator = '-'): string
        {
            return strtolower(self::splitByCase($str, $separator));
        }

        /**
         * Handle case-splitting logic using Regex.
         * Identifies boundaries between lowercase/uppercase and acronyms/words.
         *
         * @param string $str       The string to convert.
         * @param string $separator The separator to inject at boundaries.
         *
         * @return string
         */
        public static function splitByCase(string $str, string $separator = ' '): string
        {
            // 1. Lowercase followed by Uppercase (e.g., 'userGroup' -> 'user-Group')
            $str2 = preg_replace('/([a-z])([A-Z])/', "$1$separator$2", $str);

            // 2. Acronym followed by a new word (e.g., 'NASAComponents' -> 'NASA-Components')
            $result = preg_replace('/([A-Z]+)([A-Z][a-z])/', "$1$separator$2", $str2);
            return trim($result, $separator);
        }
    }

}