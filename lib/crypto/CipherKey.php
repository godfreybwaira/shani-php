<?php

/**
 *
 * Final class CipherKey
 *
 * Generates and encapsulates cryptographic key material for symmetric encryption.
 * Provides a randomly generated password (key), initialization vector (IV),
 * and the chosen encryption algorithm.
 *
 * By default, the algorithm used is `aes-256-cbc`, but any OpenSSL-supported
 * cipher algorithm can be specified.
 *
 * @package crypto
 * @author goddy
 *
 * Created on: Mar 27, 2026 at 6:36:04 PM
 */

namespace lib\crypto {

    final class CipherKey
    {

        /**
         * Base64-encoded random key generated using `openssl_random_pseudo_bytes()`.
         * Length depends on the chosen algorithm.
         *
         * @var string
         */
        public readonly string $password;

        /**
         * Base64-encoded random initialization vector (IV). Length depends on the chosen algorithm.
         *
         * @var string
         */
        public readonly string $initVector;

        /**
         * The encryption algorithm used (default: `aes-256-cbc`).
         *
         * @var string
         */
        public readonly string $algorithm;

        private function __construct(string $password, string $initVector, string $algorithm)
        {
            $this->algorithm = $algorithm;
            $this->password = $password;
            $this->initVector = $initVector;
        }

        /**
         *
         * Generates a random password and initialization vector based on the
         * specified algorithm. Both values are Base64-encoded for safe storage
         * and transport.
         *
         * @param string $algorithm The OpenSSL-supported cipher algorithm. Defaults to `aes-256-cbc`.
         *
         * @throws RuntimeException If the algorithm is unsupported or key/IV generation fails.
         * @return CipherKey Cipher key object
         */
        public static function create(string $algorithm = 'aes-256-cbc'): CipherKey
        {
            $keyLen = openssl_cipher_key_length($algorithm);
            $ivLen = openssl_cipher_iv_length($algorithm);
            if ($keyLen === false || $ivLen === false) {
                throw new \RuntimeException('Unsupported cipher algorithm: ' . $algorithm);
            }
            $passwordBase64 = base64_encode(openssl_random_pseudo_bytes($keyLen));
            $initVectorBase64 = base64_encode(openssl_random_pseudo_bytes($ivLen));
            return self::createFromValues($passwordBase64, $initVectorBase64, $algorithm);
        }

        /**
         *
         * @param string $password  Base64-encoded random key generated using `openssl_random_pseudo_bytes()`.
         * Length depends on the chosen algorithm.
         * @param string $initVector    Base64-encoded random initialization vector (IV). Length depends on the chosen algorithm.
         * @param string $algorithm The encryption algorithm used.
         * @return CipherKey Cipher key object
         */
        public static function createFromValues(string $password, string $initVector, string $algorithm): CipherKey
        {
            return new CipherKey($password, $initVector, $algorithm);
        }
    }

}
