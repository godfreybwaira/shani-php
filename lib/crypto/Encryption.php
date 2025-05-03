<?php

/**
 * Description of Encryption
 * @author coder
 *
 * Created on: Apr 28, 2025 at 9:45:27 AM
 */

namespace lib\crypto {

    interface Encryption
    {

        /**
         * Encrypt data using provided public encryption key or password
         * @param string $payload Data to encrypt
         * @return string Returns encrypted string encoded in base 64 on success.
         * @throws Exception When encryption fails
         */
        public function encrypt(string $payload): string;

        /**
         * Decrypt encrypted data using provided private decryption key or password
         * @param string $payload Encrypted data
         * @return string The decrypted string on success
         * @throws Exception When decryption fails
         */
        public function decrypt(string $payload): string;
    }

}
