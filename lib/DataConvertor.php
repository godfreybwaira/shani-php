<?php

/**
 * Description of DataConvertor
 * @author coder
 *
 * Created on: Feb 14, 2024 at 1:47:10 PM
 */

namespace lib {

    final class DataConvertor
    {

        private const SEARCH_STR = ['\\', "\"", "\n", "\r", "\t"];
        private const REPLACE_STR = ['\\\\', '\\"', "\\n", "\\r", "\\t"];
        public const TYPE_JSON = 'json';
        public const TYPE_JS = 'js';
        public const TYPE_XML = 'xml';
        public const TYPE_YAML = 'yaml';
        public const TYPE_HTML = 'html';
        public const TYPE_YML = 'yml';
        public const TYPE_CSV = 'csv';
        public const TYPE_SSE = 'event-stream';
        public const TYPE_URL_ENCODE = 'x-www-form-urlencoded';

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
         * @param string $type target data type of DataConvertor::TYPE_*.
         * @return array|null A result from conversion
         */
        public static function convertFrom(?string $data, string $type): ?array
        {
            $convertedData = match ($type) {
                self::TYPE_JSON => json_decode($data, true),
                self::TYPE_XML => self::xml2array($data),
                self::TYPE_CSV => str_getcsv($data),
                self::TYPE_URL_ENCODE => self::toUrlEncode($data),
                self::TYPE_YAML, self::TYPE_YML => self::yaml2array($data),
                default => null
            };
            return \lib\Map::normalize($convertedData);
        }

        /**
         * Convert a given data string to a compatible server sent event (SSE)
         * @param string $content Data to convert
         * @param int $retry Number of milliseconds a client should retry if the
         * server is temporarily offline
         * @return self
         */
        public static function toEventStream(string $content, int $retry = 2000, string $event = 'message'): self
        {
            $evt = 'id:id' . hrtime(true) . PHP_EOL;
            $evt .= 'retry:' . $retry . PHP_EOL;
            $evt .= 'event:' . $event . PHP_EOL;
            $evt .= 'data:' . $content . PHP_EOL;
            return $evt . PHP_EOL;
        }

        /**
         * Convert array data to string data.
         * @param array $data Data to convert
         * @param string $type target data type of DataConvertor::TYPE_*.
         * @return string A result from conversion
         */
        public static function convertTo(array $data, string $type): string
        {
            return match ($type) {
                self::TYPE_JSON => json_encode($data),
                self::TYPE_XML => self::array2xml($data),
                self::TYPE_CSV => self::array2csv($data),
                self::TYPE_URL_ENCODE => self::toUrlEncode($data),
                self::TYPE_YAML, self::TYPE_YML => yaml_emit($data),
                default => serialize($data)
            };
        }

        private static function toUrlEncode(string $data): ?string
        {
            $rawData = null;
            parse_str($data, $rawData);
            return $rawData;
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