<?php

/**
 * Description of JWT
 * @author coder
 *
 * Created on: Jun 4, 2024 at 12:26:01 PM
 */

namespace shani\engine\authorization {

    final class JWT
    {

        /**
         * Verifying if JWT token is valid
         * @param string $token Token to verify
         * @return bool Returns true if token is valid, false otherwise.
         */
        public static function verify(string $token, string $secretKey): bool
        {
            $parts = explode('.', $token, 3);
            if (!empty($parts[2])) {
                $now = time();
                $header = json_decode(base64_decode($parts[0]), true)[0];
                $invalid = $header['exp'] <= $now || $header['nbf'] > $now;
                $signature = base64_decode($parts[2]);
                $signed = hash_equals($secretKey, $signature);
                return $signed && !$invalid;
            }
            return false;
        }

        /**
         * Extract token data from token string
         * @param string $token Token to extract from
         * @param string $arrayKey value to extract, if not supplied then all values will be returned as array.
         * @return null|mixed
         */
        public static function extract(string $token, string $arrayKey = null): ?mixed
        {
            $parts = explode('.', $token, 2);
            if (!empty($parts[1])) {
                $content = json_decode(base64_decode($parts[1]), true)[0];
                return $arrayKey !== null ? $content[$arrayKey] ?? null : $content;
            }
            return null;
        }
    }

}
