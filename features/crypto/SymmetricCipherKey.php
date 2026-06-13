<?php

/**
 *
 * Final class SymmetricCipherKey
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
 * @since Mar 27, 2026 at 6:36:04 PM
 */

namespace features\crypto {

    use features\crypto\exceptions\AlgorithmException;

    final class SymmetricCipherKey
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
         * @var string|null
         */
        public readonly ?string $initVector;

        /**
         * The encryption algorithm used (default: `aes-256-cbc`).
         *
         * @var string
         */
        public readonly string $algorithm;

        private function __construct(string $password, string $algorithm, ?string $initVector = null)
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
         * @return SymmetricCipherKey Cipher key object
         */
        public static function create(string $algorithm = 'aes-256-cbc'): SymmetricCipherKey
        {
            $keyLen = openssl_cipher_key_length($algorithm);
            $ivLen = openssl_cipher_iv_length($algorithm);
            if (empty($keyLen) || empty($ivLen)) {
                throw new AlgorithmException('Unsupported cipher algorithm: ' . $algorithm);
            }
            $passwordBase64 = base64_encode(openssl_random_pseudo_bytes($keyLen));
            $initVectorBase64 = base64_encode(openssl_random_pseudo_bytes($ivLen));
            return self::createFromValues($passwordBase64, $algorithm, $initVectorBase64);
        }

        /**
         *
         * @param string                    $password  Base64-encoded random key
         * generated using `openssl_random_pseudo_bytes()`. Length depends on the chosen algorithm.
         * @param string $algorithm         The encryption algorithm used.
         * @param string|null $initVector   Base64-encoded random initialization
         * vector (IV). Length depends on the chosen algorithm.
         * @return SymmetricCipherKey       Cipher key object
         */
        public static function createFromValues(string $password, string $algorithm, ?string $initVector = null): SymmetricCipherKey
        {
            return new SymmetricCipherKey($password, $algorithm, $initVector);
        }
    }

}
