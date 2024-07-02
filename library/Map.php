<?php

/**
 * A map for manipulation of multidimensional and single-dimensional arrays
 * @author coder
 *
 * Created on: Feb 17, 2024 at 10:36:36 AM
 */

namespace library {

    final class Map
    {

        public static function orderBy(array &$rows, string $key, int $order = SORT_ASC): array
        {
            $factor = $order === SORT_DESC ? -1 : 1;
            usort($rows, function ($val1, $val2) use ($key, $factor) {
                if (is_numeric($val1[$key])) {
                    return ($val1[$key] <=> $val2[$key]) * $factor;
                }
                return strcasecmp($val1[$key], $val2[$key]) * $factor;
            });
            return $rows;
        }

        private static function found(array &$row, $needle): bool
        {
            foreach ($row as $val) {
                if (strcasecmp($needle, $val) === 0) {
                    return true;
                }
            }
            return false;
        }

        public static function groupBy(array &$rows, string $key, int $order = null): array
        {
            if ($order !== null) {
                $rows = self::orderBy($rows, $key, $order);
            }
            $results = [];
            foreach ($rows as $row) {
                if (self::found($row, $row[$key])) {
                    $results[strtoupper($row[$key])][] = $row;
                }
            }
            return $results;
        }

        /**
         * Find a row on multi-dimension array and apply a callback on it.
         * @param array $rows
         * @param callable $cb A callback to execute on each row of array. If a row
         * matches a given condition, this callback must return true, otherwise false.
         * @param int $limit number of records to return
         * @return array Matched array
         */
        public static function find(array $rows, callable $cb, int $limit = 0): array
        {
            $size = 0;
            $result = [];
            foreach ($rows as $row) {
                if (!$cb($row)) {
                    continue;
                }
                $result[$size++] = $row;
                if ($size === $limit) {
                    break;
                }
            }
            return $result;
        }

        /**
         * Filter array and return the filtered array
         * @param array $rows Array to filter from
         * @param array $filters Array of keys and values to be used on filtering.
         * @return array filtered array
         */
        public static function filter(array $rows, array $filters = null): array
        {
            if (empty($filters)) {
                return $rows;
            }
            $data = [];
            self::find($rows, function ($row) use (&$filters, &$data) {
                if (self::hasValues($row, $filters)) {
                    $data[] = $row;
                }
            });
            return $data;
        }

        /**
         * Convert array string values to their internal representation. For example
         * a string value 'null' is converted to NULL, string 'true' or 'false' is
         * converted to boolean value true and false respectively etc.
         * @param array|null $values array to normalize
         * @return array|null normalized array
         */
        public static function normalize(?array $values): ?array
        {
            if ($values === null) {
                return $values;
            }
            $content = [];
            foreach ($values as $key => $val) {
                if (is_array($val)) {
                    $content[$key] = self::normalize($val);
                } elseif ($val === 'null') {
                    $content[$key] = null;
                } elseif ($val === 'true' || $val === 'false') {
                    $content[$key] = ($val === 'true');
                } elseif (preg_match('/^\d+$/', $val)) {
                    $content[$key] = (int) $val;
                } elseif (preg_match('/^\d*\.\d+/$', $val)) {
                    $content[$key] = (double) $val;
                } else {
                    $content[$key] = $val;
                }
            }
            return $content;
        }

        /**
         * Get rows from a multidimensional array
         * @param array $rows
         * @param array $keys A key or array of keys to be used as filter
         * @param bool $selected If true will mean getting only returning arrays
         * with selected keys, otherwise will return arrays NOT from selected keys.
         * @return array found arrays
         */
        public static function getAll(array $rows, array $keys, bool $selected = true): array
        {
            $result = [];
            foreach ($rows as &$row) {
                $result[] = self::get($row, $keys, $selected);
            }
            return $result;
        }

        /**
         * Get a value from array
         * @param array|null $row single-dimensional array of items to get values from
         * @param type $keys A key or array of keys to be used as filter
         * @param bool $selected If true will mean getting only returning arrays
         * with selected keys, otherwise will return arrays NOT from selected keys.
         * @return type A value(s) that was found in array
         */
        public static function get(?array $row, $keys = null, bool $selected = true)
        {
            if ($keys === null || $row === null) {
                return $selected ? $row : [];
            }
            if (is_array($keys)) {
                if (!$selected) {
                    return array_filter($row, fn($key) => !in_array($key, $keys), ARRAY_FILTER_USE_KEY);
                }
                $result = [];
                foreach ($keys as $idx => $val) {
                    if (is_int($idx)) {
                        $result[$val] = $row[$val] ?? null;
                    } else {
                        $result[$idx] = $row[$idx] ?? $val;
                    }
                }
                return $result;
            }
            return $selected ? $row[$keys] ?? null : self::get($row, [$keys], $selected);
        }

        public static function add(array $source, array $destination, $keys = null, bool $selected = true): array
        {
            return array_merge($destination, self::get($source, is_array($keys) ? $keys : [$keys], $selected));
        }

        public static function remove(array &$source, ?array $keys = null, bool $selected = true): void
        {
            $source = $keys !== null ? static::get($source, $keys, $selected) : null;
        }

        public static function hasKeys(array $data, $keys): bool
        {
            if (is_array($keys)) {
                foreach ($keys as $k) {
                    if (!array_key_exists($k, $data)) {
                        return false;
                    }
                }
                return true;
            }
            return array_key_exists($keys, $data);
        }

        public static function hasValues(array $row, array $values): bool
        {
            foreach ($values as $key => $val) {
                if (!array_key_exists($key, $row) || $row[$key] !== $val) {
                    return false;
                }
            }
            return true;
        }

        public static function reduce(array &$rows, callable $cb, $initialValue = null)
        {
            $accumulator = $initialValue;
            foreach ($rows as &$row) {
                $accumulator = $cb($row, $accumulator);
            }
            return $accumulator;
        }

        public static function each(array &$rows, callable $cb): array
        {
            foreach ($rows as &$row) {
                $row = $cb($row);
            }
            return $rows;
        }
    }

}
