<?php

/**
 * Description of SymmetricEncryption
 * @author coder
 *
 * Created on: Apr 28, 2025 at 9:45:27 AM
 */

namespace features\crypto {

    final class SymmetricEncryption implements Encryption
    {

        /**
         * Cipher key object
         * @var CipherKey
         */
        public readonly CipherKey $cipher;

        /**
         * Encrypt/decrypt data using symmetric keys
         * @param CipherKey $cipher Cipher key object
         */
        public function __construct(CipherKey $cipher)
        {
            $this->cipher = $cipher;
        }

        public function encrypt(string $payload): string
        {
            $result = openssl_encrypt($payload, $this->cipher->algorithm, $this->cipher->password, 0, $this->cipher->initVector);
            if ($result !== false) {
                return $result;
            }
            throw new \Exception('Failed to encrypt data');
        }

        public function decrypt(string $payload): string
        {
            $result = openssl_decrypt($payload, $this->cipher->algorithm, $this->cipher->password, 0, $this->cipher->initVector);
            if ($result !== false) {
                return $result;
            }
            throw new \Exception('Failed to decrypt data');
        }
    }

}
