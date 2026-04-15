<?php

/**
 * Description of SymmetricSignature
 * @author coder
 *
 * Created on: Apr 28, 2025 at 11:44:18 AM
 */

namespace features\crypto {

    final class SymmetricSignature implements DigitalSignature
    {

        /**
         * The private key used for generating the signature
         * @var string
         */
        public readonly string $password;

        /**
         * The hashing algorithm used.
         * @var CryptoAlgorithm
         */
        public readonly CryptoAlgorithm $algorithm;

        /**
         * This class provides a method to sign and verify data integrity using HMAC
         * (Hash-based Message Authentication Code). It ensures that a payload remains
         * unchanged and authenticated by using a secret key.
         * @param string $password  The private key used for generating the signature
         * @param CryptoAlgorithm   $algorithm Cryptographic algorithm
         */
        public function __construct(string $password, CryptoAlgorithm $algorithm = CryptoAlgorithm::SHA256)
        {
            $this->password = $password;
            $this->algorithm = $algorithm;
        }

        public function sign(string $payload): string
        {
            return hash_hmac($this->algorithm->value, $payload, $this->password);
        }

        public function verify(string $payload, ?string $signature): bool
        {
            if (empty($signature)) {
                throw new \Exception('Signature is missing or empty.');
            }
            return hash_equals($this->sign($payload), $signature);
        }

        /**
         * Generates a unique random digital signature.
         * @param int $length Byte length
         * @return string Encodes signature using base 64 format.
         */
        public static function createSignature(int $length = 32): string
        {
            return base64_encode(random_bytes($length));
        }
    }

}
