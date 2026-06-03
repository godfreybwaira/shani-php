<?php

/**
 * Asymmetric key-pair class
 * @author goddy
 *
 * Created on: Mar 27, 2026 at 6:00:45 PM
 */

namespace features\crypto {

    final class AsymmetricKeyPair implements \JsonSerializable
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
         * Private key type.
         * @var string|null
         */
        private readonly ?string $keyType;

        /**
         * Create an asymmetric key-pair object
         * @param string $privateKey Secret key used on decryption.
         * @param string $publicKey Used for encryption.
         * @param int|null $keyType Private key type.
         */
        public function __construct(string $privateKey, string $publicKey, ?int $keyType = null)
        {
            $this->privateKey = $privateKey;
            $this->publicKey = $publicKey;
            $this->keyType = self::getKeyType($keyType);
        }

        /**
         * Generates asymmetric key pairs (Private & Public keys .pem files).
         * @param array $configs
         * @return AsymmetricKeyPair
         */
        private static function generate(array $configs): AsymmetricKeyPair
        {
            $privateKey = null;
            $resource = openssl_pkey_new($configs);
            openssl_pkey_export($resource, $privateKey);
            $publicKey = openssl_pkey_get_details($resource)['key'];
            return new AsymmetricKeyPair($privateKey, $publicKey, $configs['private_key_type']);
        }

        /**
         * Generate an <code>ED25519</code> asymmetric key pair
         *
         * Built for modern cryptographic needs, balancing speed, security, and simplicity.
         * @return AsymmetricKeyPair
         */
        public static function ed25519(): AsymmetricKeyPair
        {
            if (!defined('OPENSSL_KEYTYPE_ED25519')) {
                define('OPENSSL_KEYTYPE_ED25519', 5); // 5 is the expected internal value for Ed25519
            }
            return self::generate(['private_key_type' => OPENSSL_KEYTYPE_ED25519]);
        }

        /**
         * Generate an <code>RSA</code> asymmetric key pair
         *
         * @param int|null $keySize Key size. Must be a multiple of 128, higher value
         * means more secure but slower. Default is 2048
         * @param CryptoAlgorithm|null $algorithm The hashing algorithm. Default is sha256
         * @return AsymmetricKeyPair
         * @throws \InvalidArgumentException
         */
        public static function rsa(?int $keySize = null, ?CryptoAlgorithm $algorithm = null): AsymmetricKeyPair
        {
            $length = $keySize ?? 2048;
            if ($length % 128 !== 0) {
                throw new \InvalidArgumentException('Key size must be a multiple of 128.');
            }
            if (!defined('OPENSSL_KEYTYPE_RSA')) {
                define('OPENSSL_KEYTYPE_RSA', 0); // The integer value for OPENSSL_KEYTYPE_RSA is 0
            }
            $configs = [
                'digest_alg' => $algorithm?->value ?? CryptoAlgorithm::SHA256->value,
                'private_key_bits' => $length,
                'private_key_type' => OPENSSL_KEYTYPE_RSA
            ];
            return self::generate($configs);
        }

        /**
         * Generate an <code>ECDSA</code> asymmetric key pair
         *
         * @param string|null $curveName Curve name. Default is prime256v1
         * @return AsymmetricKeyPair
         *
         * @see openssl_get_curve_names()
         */
        public static function ecdsa(?string $curveName = null): AsymmetricKeyPair
        {
            if (!defined('OPENSSL_KEYTYPE_EC')) {
                define('OPENSSL_KEYTYPE_EC', 3); // The integer value for OPENSSL_KEYTYPE_EC is 3
            }
            $configs = [
                'curve_name' => $curveName ?? 'prime256v1',
                'private_key_type' => OPENSSL_KEYTYPE_EC
            ];
            return self::generate($configs);
        }

        public function jsonSerialize(): array
        {
            return [
                'private_key' => $this->privateKey,
                'public_key' => $this->publicKey,
                'key_type' => $this->keyType
            ];
        }

        private static function getKeyType(?int $keyType): ?string
        {
            return match ($keyType) {
                3 => 'ecdsa',
                0 => 'rsa',
                5 => 'ed25519',
                null => null,
                default => throw new \InvalidArgumentException('Unsupported key type')
            };
        }
    }

}
