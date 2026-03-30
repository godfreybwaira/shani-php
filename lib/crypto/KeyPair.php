<?php

/**
 * Asymmetric key-pair class
 * @author goddy
 *
 * Created on: Mar 27, 2026 at 6:00:45 PM
 */

namespace lib\crypto {

    final class KeyPair implements \JsonSerializable
    {

        /**
         * Secret decryption key.
         * @var string
         */
        public readonly string $privateKey;

        /**
         * Public encryption key.
         * @var string
         */
        public readonly string $publicKey;

        /**
         * Create an asymmetric key-pair object
         * @param string $privateKey Secret key used on decryption.
         * @param string $publicKey Used for encryption.
         */
        public function __construct(string $privateKey, string $publicKey)
        {
            $this->privateKey = $privateKey;
            $this->publicKey = $publicKey;
        }

        /**
         * Generates asymmetric key pairs (Private & Public keys .pem files).
         * @param array $configs
         * @return KeyPair
         */
        private static function generate(array $configs): KeyPair
        {
            $privateKey = null;
            $resource = openssl_pkey_new($configs);
            openssl_pkey_export($resource, $privateKey);
            $publicKey = openssl_pkey_get_details($resource)['key'];
            return new KeyPair($privateKey, $publicKey);
        }

        /**
         * Generate an <code>ED25519</code> asymmetric key pair
         *
         * Built for modern cryptographic needs, balancing speed, security, and simplicity.
         * @return KeyPair
         */
        public static function ed25519(): KeyPair
        {
            if (!defined('OPENSSL_KEYTYPE_ED25519')) {
                define('OPENSSL_KEYTYPE_ED25519', 5); // 5 is the expected internal value for Ed25519
            }
            return self::generate(['private_key_type' => OPENSSL_KEYTYPE_ED25519]);
        }

        /**
         * Generate an <code>RSA</code> asymmetric key pair
         *
         * @param int $keySize Key size. Must be a multiple of 128, higher value
         * means more secure but slower.
         * @param CryptoAlgorithm $algorithm The hashing algorithm.
         * @return KeyPair
         * @throws \InvalidArgumentException
         */
        public static function rsa(int $keySize = 2048, CryptoAlgorithm $algorithm = CryptoAlgorithm::SHA256): KeyPair
        {
            if ($keySize % 128 !== 0) {
                throw new \InvalidArgumentException('Key size must be a multiple of 128.');
            }
            if (!defined('OPENSSL_KEYTYPE_RSA')) {
                define('OPENSSL_KEYTYPE_RSA', 0); // The integer value for OPENSSL_KEYTYPE_RSA is 0
            }
            $configs = [
                'digest_alg' => $algorithm->value,
                'private_key_bits' => $keySize,
                'private_key_type' => OPENSSL_KEYTYPE_RSA
            ];
            return self::generate($configs);
        }

        /**
         * Generate an <code>ECDSA</code> asymmetric key pair
         *
         * @param string $curveName Curve name
         * @return KeyPair
         *
         * @see openssl_get_curve_names()
         */
        public static function ecdsa(string $curveName = 'prime256v1'): KeyPair
        {
            if (!defined('OPENSSL_KEYTYPE_EC')) {
                define('OPENSSL_KEYTYPE_EC', 3); // The integer value for OPENSSL_KEYTYPE_EC is 3
            }
            $configs = [
                'curve_name' => $curveName,
                'private_key_type' => OPENSSL_KEYTYPE_EC
            ];
            return self::generate($configs);
        }

        public function jsonSerialize(): array
        {
            return [
                'private_key' => $this->privateKey,
                'public_key' => $this->publicKey
            ];
        }
    }

}
