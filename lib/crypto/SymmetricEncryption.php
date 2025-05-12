<?php

/**
 * Description of SymmetricEncryption
 * @author coder
 *
 * Created on: Apr 28, 2025 at 9:45:27 AM
 */

namespace lib\crypto {

    final class SymmetricEncryption implements Encryption
    {

        public readonly string $password, $algorithm, $initVector;

        /**
         * Encrypt/decrypt data using symmetric keys
         * @param string $password Password used to encrypt/decrypt data.
         * @param string $initVector Initialization vector encoded in base 64
         * @param string $algorithm see openssl_get_cipher_methods()
         */
        public function __construct(string $password, string $initVector, string $algorithm = 'aes-256-cbc')
        {
            $this->password = $password;
            $this->algorithm = $algorithm;
            $this->initVector = $initVector;
        }

        public function encrypt(string $payload): string
        {
            $result = openssl_encrypt($payload, $this->algorithm, $this->password, 0, $this->initVector);
            if ($result !== false) {
                return $result;
            }
            throw new \Exception('Failed to encrypt data');
        }

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
