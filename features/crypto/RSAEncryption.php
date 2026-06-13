<?php

/**
 * RSAEncryption
 *
 * Provides RSA-based asymmetric encryption and decryption functionality.
 * This class requires an RSA key pair (public/private) to operate.
 *
 * @author coder
 * @since Apr 28, 2025 at 9:45:27 AM
 */

namespace features\crypto {

    final class RSAEncryption implements Encryption
    {

        /**
         * @var AsymmetricKeyPair $keys
         * The RSA key pair used for encryption and decryption.
         */
        public readonly AsymmetricKeyPair $keys;

        /**
         * Initializes the encryption class with a given RSA key pair.
         */
        public function __construct(AsymmetricKeyPair $keys)
        {
            $this->keys = $keys;
        }

        public function encrypt(string $payload): string
        {
            $publicKey = openssl_pkey_get_public($this->keys->publicKey);
            if (!$publicKey) {
                throw new \RuntimeException('Invalid public key');
            }
            $details = openssl_pkey_get_details($publicKey);
            $keySize = $details['bits'] / 8;
            $maxLength = $keySize - 42; // Padding overhead: OAEP with SHA-1 uses 42 bytes
            if (strlen($payload) > $maxLength) {
                throw new \RuntimeException('Maximum payload size of ' . $maxLength . ' bytes exceeded.');
            }
            $result = null;
            if (openssl_public_encrypt($payload, $result, $publicKey, OPENSSL_PKCS1_OAEP_PADDING)) {
                openssl_free_key($publicKey);
                return base64_encode($result);
            }
            throw new \RuntimeException('Failed to encrypt data');
        }

        public function decrypt(string $payload): string
        {
            $privateKey = openssl_pkey_get_private($this->keys->privateKey);
            if (!$privateKey) {
                throw new \RuntimeException('Invalid private key');
            }
            $result = null;
            if (openssl_private_decrypt(base64_decode($payload), $result, $privateKey, OPENSSL_PKCS1_OAEP_PADDING)) {
                openssl_free_key($privateKey);
                return $result;
            }
            throw new \RuntimeException('Failed to decrypt data');
        }
    }

}
