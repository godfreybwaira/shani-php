<?php

/**
 * ECDSA Helpers for DER/Raw Conversion
 * @author goddy
 *
 * Created on: Mar 30, 2026 at 12:19:26 AM
 */

namespace lib\jwt {

    final class ECDSAHelper
    {

        public static function der2Sig(string $der): string
        {
            $parts = self::parseDer($der);
            // ES256 expects exactly 64 bytes (32 for R, 32 for S)
            return str_pad($parts[0], 32, "\x00", STR_PAD_LEFT) .
                    str_pad($parts[1], 32, "\x00", STR_PAD_LEFT);
        }

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
