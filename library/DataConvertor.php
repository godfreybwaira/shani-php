<?php

/**
 * Description of DataConvertor
 * @author coder
 *
 * Created on: Feb 14, 2024 at 1:47:10 PM
 */

namespace library {

    final class DataConvertor
    {

        private const SEARCH_STR = ['\\', "\"", "\n", "\r", "\t"];
        private const REPLACE_STR = ['\\\\', '\\"', "\\n", "\\r", "\\t"];

        /**
         * Convert normal array to table like array. A table like array has two values
         * where value on index 0 has array keys as headers and index 1 has array
         * values as table body.
         * @param array $rows Array to convert
         * @param array $headers Associative array whose keys must match with array keys
         * and value becomes table headers (columns names)
         * @return array A converted array
         */
        public static function array2table(array $rows, array $headers): array
        {
            $table = [];
            $isArray = isset($rows[0]) && is_array($rows[0]);
            $data = $isArray ? $rows[0] : $rows;
            foreach ($headers as $key => $value) {
                $idx = is_int($key) ? $value : $key;
                if (!array_key_exists($idx, $data)) {
                    continue;
                }
                $table[0][] = $value;
                if ($isArray) {
                    foreach ($rows as $r => $row) {
                        $table[1][$r][] = $row[$idx];
                    }
                } else {
                    $table[1][0][] = $rows[$idx];
                }
            }
            return $table;
        }

        /**
         * Convert array to XML data format
         * @param array|null $data Data to convert
         * @return string|null converted data as XML
         */
        public static function array2xml(?array $data): ?string
        {
            return $data !== null ? '<?xml version="1.0"?>' . self::toxml($data, 'data') : $data;
        }

        /**
         * Convert array to CSV data format
         * @param array|null $data Data to convert
         * @return string|null converted data as CSV
         */
        public static function array2csv(?array $data, string $separator = ','): ?string
        {
            return $data !== null ? self::tocsv($data, $separator) : $data;
        }

        /**
         * Convert array to YAML data format
         * @param array|null $data Data to convert
         * @return string|null converted data as YAML
         */
        public static function array2yaml(?array $data): ?string
        {
            return $data !== null ? '---' . PHP_EOL . self::toyaml($data) . '...' : $data;
        }

        /**
         * Convert XML data to array
         * @param string|null $data XML data to convert
         * @return array|null A result from conversion
         */
        public static function xml2array(?string $data): ?array
        {
            if (($xml = simplexml_load_string($data)) !== false) {
                return json_decode(json_encode($xml), true);
            }
            return null;
        }

        /**
         * Convert YAML data to array
         * @param string|null $data YAML data to convert
         * @return array|null A result from conversion
         */
        public static function yaml2array(?string $data): ?array
        {
            if (($yaml = yaml_parse($data)) !== false) {
                return $yaml;
            }
            return null;
        }

        /**
         * Convert string data to array.
         * @param string|null $data Data to convert
         * @param string $type target data type. Can be any of the following:
         * json, xml, csv or yaml.
         * @return array|null A result from conversion
         */
        public static function convertFrom(?string $data, string $type): ?array
        {
            switch ($type) {
                case'json':
                    return json_decode($data, true);
                case'xml':
                    return self::xml2array($data);
                case'csv':
                    return str_getcsv($data);
                case'yaml':
                case'yml':
                    return self::yaml2array($data);
            }
            return null;
        }

        /**
         * Convert array data to string data.
         * @param array $data Data to convert
         * @param string $type target data type. Can be any of the following:
         * json, xml, csv or yaml.
         * @return string A result from conversion
         */
        public static function convertTo(array $data, string $type): string
        {
            switch ($type) {
                case'json':
                    return json_encode($data);
                case'xml':
                    return self::array2xml($data);
                case'csv':
                    return self::array2csv($data);
                case'yaml':
                case'yml':
                    return yaml_emit($data);
            }
            return serialize($data);
        }

        private static function toyaml(array $obj, int $indent = 0): string
        {
            $yaml = '';
            foreach ($obj as $key => $value) {
                $yaml .= str_repeat('  ', $indent) . $key . ': ';
                if (is_array($value)) {
                    $yaml .= PHP_EOL . self::toyaml($value, $indent + 1);
                } else if (is_bool($value)) {
                    $yaml .= ($value ? 'true' : 'false') . PHP_EOL;
                } else if (is_numeric($value)) {
                    $yaml .= $value . PHP_EOL;
                } else {
                    $yaml .= '"' . str_replace(self::SEARCH_STR, self::REPLACE_STR, $value) . '"' . PHP_EOL;
                }
            }
            return $yaml;
        }

        private static function tocsv(array $obj, string $separator): ?string
        {
            $csv = is_array($obj[0]) ? '"' . implode('"' . $separator . '"', array_keys($obj[0])) . '"' : null;
            foreach ($obj as $values) {
                $row = null;
                foreach ($values as $val) {
                    $value = is_array($val) ? implode('|', $val) : $val;
                    $row .= $separator . '"' . str_replace(self::SEARCH_STR, self::REPLACE_STR, $value) . '"';
                }
                $csv .= PHP_EOL . substr($row, strlen($separator));
            }
            return $csv;
        }

        private static function toxml($obj, $tagName): string
        {
            $tag = preg_replace('/[ ]+/', '-', $tagName);
            $xml = '<' . $tag . '>';
            if (is_array($obj)) {
                foreach ($obj as $key => $val) {
                    $xml .= self::toxml($val, is_int($key) ? 'item' : $key);
//                    $xml .= self::toxml($val, is_int($key) ? $tag . ($key + 1) : $key);
                }
            } else if (is_bool($obj)) {
                $xml .= $obj ? 'true' : 'false';
            } else if ($obj === null) {
                $xml .= 'null';
            } else {
                $xml .= $obj;
            }
            return $xml . '</' . $tag . '>';
        }
    }

}