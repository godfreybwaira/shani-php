<?php

/**
 * ECDSA Helpers for DER/Raw Conversion
 *
 * Provides utility methods to convert between DER-encoded ECDSA signatures
 * and raw concatenated R+S formats (commonly used in JWT ES256/ES384/ES512).
 *
 * @author goddy
 * @since Mar 30, 2026 at 12:19:26 AM
 */

namespace features\jwt {

    final class ECDSAHelper
    {

        /**
         * Convert a DER-encoded ECDSA signature into raw concatenated format.
         *
         * The raw format is expected by JWT ES256/ES384/ES512, where
         * R and S are each padded to fixed lengths (e.g., 32 bytes for ES256).
         *
         * @param string $der DER-encoded signature
         * @return string Raw signature (R+S concatenated, padded to fixed length)
         */
        public static function der2Sig(string $der): string
        {
            $parts = self::parseDer($der);
            // ES256 expects exactly 64 bytes (32 for R, 32 for S)
            return str_pad($parts[0], 32, "\x00", STR_PAD_LEFT) .
                    str_pad($parts[1], 32, "\x00", STR_PAD_LEFT);
        }

        /**
         * Convert a raw concatenated ECDSA signature into DER format.
         *
         * Raw format contains R and S values concatenated together.
         * This method ensures ASN.1 encoding rules are followed,
         * including prepending a leading zero if the integer would
         * otherwise be interpreted as negative.
         *
         * @param string $sig Raw signature (R+S concatenated)
         * @return string DER-encoded signature
         */
        public static function sig2Der(string $sig): string
        {
            $r = ltrim(substr($sig, 0, 32), "\x00");
            $s = ltrim(substr($sig, 32), "\x00");
            // Ensure the integers are treated as positive in ASN.1
            if (ord($r[0]) > 127) {
                $r = "\x00" . $r;
            }
            if (ord($s[0]) > 127) {
                $s = "\x00" . $s;
            }
            return "\x30" . chr(strlen($r) + strlen($s) + 4) .
                    "\x02" . chr(strlen($r)) . $r .
                    "\x02" . chr(strlen($s)) . $s;
        }

        /**
         * Parse a DER-encoded ECDSA signature into its R and S components.
         *
         * This method extracts the integer values from the ASN.1 sequence
         * and trims leading zeros.
         *
         * @param string $der DER-encoded signature
         * @return array{0:string,1:string} Array containing R and S values
         */
        private static function parseDer(string $der): array
        {
            $offset = 2; // Skip sequence header
            $rLen = ord($der[$offset + 1]);
            $r = substr($der, $offset + 2, $rLen);
            $offset += 2 + $rLen;
            $sLen = ord($der[$offset + 1]);
            $s = substr($der, $offset + 2, $sLen);
            return [ltrim($r, "\x00"), ltrim($s, "\x00")];
        }
    }

}
