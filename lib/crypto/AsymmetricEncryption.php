<?php

/**
 * Description of AsymmetricEncryption
 * @author coder
 *
 * Created on: Apr 28, 2025 at 9:45:27 AM
 */

namespace lib\crypto {

    final class AsymmetricEncryption implements Encryption
    {

        public readonly string $privateKey, $publicKey;

        /**
         * Encrypt/decrypt data using asymmetric keys
         * @param string $privateKey Used for decrypting payloads.
         * @param string $publicKey Used for encrypting payloads.
         */
        public function __construct(string $privateKey, string $publicKey)
        {
            $this->privateKey = $privateKey;
            $this->publicKey = $publicKey;
        }

        /**
         * Encrypt data using provided public encryption key
         * @param string $payload Data to encrypt
         * @return string Returns the encrypted string on success.
         * @throws Exception
         */
        public function encrypt(string $payload): string
        {
            $result = null;
            if (openssl_public_encrypt($payload, $result, $this->publicKey)) {
                return base64_encode($result);
            }
            throw new \Exception('Failed to encrypt data');
        }

        /**
         * Decrypt encrypted data using provided private decryption keys
         * @param string $payload Encrypted data
         * @return string The decrypted string on success
         * @throws Exception
         */
        public function decrypt(string $payload): string
        {
            $result = null;
            if (openssl_private_decrypt(base64_decode($payload), $result, $this->privateKey)) {
                return $result;
            }
            throw new \Exception('Failed to decrypt data');
        }
    }

}
