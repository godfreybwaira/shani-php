<?php

/**
 * Description of DataCompression
 * @author coder
 *
 * Created on: Mar 5, 2025 at 10:48:56 AM
 */

namespace lib {

    enum DataCompression: int
    {

        case LOWEST = 1;
        case LOWER = 2;
        case LOW = 3;
        case BETTER = 4;
        case BEST = 5;
        case GOOD = 6;
        case BAD = 7;
        case WORSE = 8;
        case WORST = 9;

        /**
         * Decompress data using gzip, deflate or compress algorithms
         * @param string $data Compressed data
         * @param string $encoding list of optional encoding to choose from
         * @return string Decompressed string
         * @throws \Exception Throws exception if encoding algorithm is not supported.
         */
        public static function decompress(string $data, string $encoding): string
        {
            if (str_contains($encoding, 'gzip')) {
                return gzdecode($data);
            }
            if (str_contains($encoding, 'deflate')) {
                return gzinflate($data);
            }
            if (str_contains($encoding, 'compress')) {
                return gzuncompress($data);
            }
            throw new \Exception('Encoding algorithm not supported.');
        }

        /**
         * Compress data using supported algorithms i.e gzip, deflate and compress
         * @param string $data Data to compress
         * @param string $encoding list of optional encoding to choose from
         * @param DataCompression $level Compression level
         * @return string Compressed string
         * @throws \Exception Throws exception if encoding algorithm is not supported.
         */
        public static function compress(string $data, string $encoding, DataCompression $level): string
        {
            if (str_contains($encoding, 'gzip')) {
                return gzencode($data, $level->value);
            }
            if (str_contains($encoding, 'deflate')) {
                return gzdeflate($data, $level->value);
            }
            if (str_contains($encoding, 'compress')) {
                return gzcompress($data, $level->value);
            }
            throw new \Exception('Encoding algorithm not supported.');
        }

        /**
         * Get supported encoding algorithm
         * @param string $encoding list of optional encoding to choose from
         * @return string|null Return null if algorithm is not supported
         */
        public static function algorithm(string $encoding): ?string
        {
            if (str_contains($encoding, 'gzip')) {
                return 'gzip';
            }
            if (str_contains($encoding, 'deflate')) {
                return 'deflate';
            }
            if (str_contains($encoding, 'compress')) {
                return 'compress';
            }
            return null;
        }
    }

}
