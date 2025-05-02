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

        public readonly string $privateKey, $algorithm, $publicKey;

        /**
         * This class provides methods to sign and verify digital signatures
         * using OpenSSL. It uses asymmetric cryptography with a private and
         * public key pair for secure authentication.
         * @param string $privateKey Used for signing payloads.
         * @param string $publicKey Used for verifying signatures.
         * @param string $algorithm Specifies the hashing algorithm (e.g., SHA256, SHA512, etc.).
         * @see openssl_get_md_methods()
         */
        public function __construct(string $privateKey, string $publicKey, string $algorithm = 'sha256')
        {
            $this->privateKey = $privateKey;
            $this->publicKey = $publicKey;
            $this->algorithm = $algorithm;
        }

        public function sign(string $payload): string
        {
            $signature = null;
            $privateKey = openssl_pkey_get_private($this->privateKey);
            if (openssl_sign($payload, $signature, $privateKey, $this->algorithm)) {
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
            $publicKey = openssl_pkey_get_public($this->publicKey);
            $verified = openssl_verify($payload, base64_decode($signature), $publicKey, $this->algorithm);
            if ($verified === 1) {
                return true;
            }
            throw new \Exception('Invalid signature.');
        }
    }

}
