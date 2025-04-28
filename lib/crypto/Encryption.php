<?php

/**
 * Description of Encryption
 * @author coder
 *
 * Created on: Apr 28, 2025 at 9:45:27 AM
 */

namespace lib\crypto {

    final class Encryption
    {

        public readonly string $password, $algorithm, $initVector;

        public function __construct(string $password, string $initVector, string $algorithm = 'aes-256-cbc')
        {
            $this->password = $password;
            $this->algorithm = $algorithm;
            $this->initVector = $initVector;
        }

        /**
         * Encrypt data using provided encryption credentials
         * @param string $payload Data to encrypt
         * @return string Returns the encrypted string on success.
         */
        public function encrypt(string $payload): string|false
        {
            $result = openssl_encrypt($payload, $this->algorithm, $this->password, 0, $this->initVector);
            if ($result !== false) {
                return $result;
            }
            throw new \Exception('Failed to encrypt data');
        }

        /**
         * Decrypt once encrypted data using provided decryption keys
         * @param string $payload Encrypted data
         * @return string The decrypted string on success
         */
        public function decrypt(string $payload): string
        {
            $result = openssl_decrypt($payload, $this->algorithm, $this->password, 0, $this->initVector);
            if ($result !== false) {
                return $result;
            }
            throw new \Exception('Failed to decrypt data');
        }
    }

}
