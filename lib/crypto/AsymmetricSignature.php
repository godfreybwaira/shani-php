<?php

/**
 * Description of AsymmetricSignature
 * @author coder
 *
 * Created on: Apr 28, 2025 at 11:44:18 AM
 */

namespace lib\crypto {

    final class AsymmetricSignature implements DigitalSignature
    {

        public readonly KeyPair $keys;
        public readonly CryptoAlgorithm $algorithm;

        /**
         * This class provides methods to sign and verify digital signatures
         * using OpenSSL. It uses asymmetric cryptography with a private and
         * public key pair for secure authentication.
         * @param KeyPair           $keys key pair object.
         * @param CryptoAlgorithm   $algorithm Cryptographic algorithm
         */
        public function __construct(KeyPair $keys, CryptoAlgorithm $algorithm = CryptoAlgorithm::SHA256)
        {
            $this->keys = $keys;
            $this->algorithm = $algorithm;
        }

        public function sign(string $payload): string
        {
            $signature = null;
            $privateKey = openssl_pkey_get_private($this->keys->privateKey);
            if (openssl_sign($payload, $signature, $privateKey, $this->algorithm->value)) {
                openssl_free_key($privateKey);
                return base64_encode($signature);
            }
            throw new \Exception('Failed to sign a payload.');
        }

        public function verify(string $payload, ?string $signature): bool
        {
            if (empty($signature)) {
                throw new \Exception('Signature is missing or empty.');
            }
            $publicKey = openssl_pkey_get_public($this->keys->publicKey);
            $verified = openssl_verify($payload, base64_decode($signature), $publicKey, $this->algorithm->value);
            return $verified === 1;
        }
    }

}
