<?php

/**
 * Encryption Interface
 *
 * Defines a contract for encryption and decryption operations.
 * Implementations may use symmetric (password-based) or asymmetric
 * (public/private key) algorithms to secure data.
 *
 * @author coder
 * @since Apr 28, 2025 at 9:45:27 AM
 */

namespace features\crypto {

    interface Encryption
    {

        /**
         * Encrypt data using a provided public key or password.
         *
         * Implementations should ensure strong encryption algorithms
         * are used and return the result encoded in Base64 for safe
         * transmission or storage.
         *
         * @param string $payload Plaintext data to encrypt
         * @return string Encrypted data encoded in Base64
         * @throws \Exception If encryption fails or key is invalid
         */
        public function encrypt(string $payload): string;

        /**
         * Decrypt previously encrypted data using a private key or password.
         *
         * Implementations should correctly handle Base64 decoding
         * before applying the decryption algorithm.
         *
         * @param string $payload Base64-encoded encrypted data
         * @return string Decrypted plaintext string
         * @throws \Exception If decryption fails or key is invalid
         */
        public function decrypt(string $payload): string;
    }

}
