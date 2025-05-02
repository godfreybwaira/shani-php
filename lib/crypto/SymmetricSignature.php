<?php

/**
 * Description of SymmetricSignature
 * @author coder
 *
 * Created on: Apr 28, 2025 at 11:44:18 AM
 */

namespace lib\crypto {

    final class SymmetricSignature implements DigitalSignature
    {

        /**
         * The private key used for generating the signature
         * @var string
         */
        public readonly string $password;

        /**
         * The hashing algorithm used (e.g., SHA256, SHA512, etc.).
         * @var string
         */
        public readonly string $algorithm;

        /**
         * This class provides a method to sign and verify data integrity using HMAC
         * (Hash-based Message Authentication Code). It ensures that a payload remains
         * unchanged and authenticated by using a secret key.
         * @param string $password The private key used for generating the signature
         * @param string $algorithm The hashing algorithm used (e.g., SHA256, SHA512, etc.).
         * @see @see hash_hmac_algos()
         */
        public function __construct(string $password, string $algorithm = 'sha256')
        {
            $this->password = $password;
            $this->algorithm = $algorithm;
        }

        public function sign(string $payload): string
        {
            return hash_hmac($this->algorithm, $payload, $this->password);
        }

        public function verify(string $payload, ?string $signature): bool
        {
            if (empty($signature)) {
                throw new \Exception('Signature is missing or empty.');
            }
            if (!hash_equals($this->sign($payload), $signature)) {
                throw new \Exception('Invalid signature.');
            }
            return true;
        }
    }

}
