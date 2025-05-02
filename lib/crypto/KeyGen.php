<?php

/**
 * This class provides cryptographic utilities for key generation, cipher key
 * creation, and signature generation. It supports multiple cryptographic standards,
 * including RSA, ECDSA, and Ed25519, ensuring flexible security solutions.
 * @author coder
 *
 * Created on: Apr 28, 2025 at 9:57:48 AM
 */

namespace lib\crypto {

    final class KeyGen
    {

        /**
         * Generates encryption keys (key and IV) for using in symmetric encryption (e.g: AES).
         * @param string $algorithm Any algorithm accepted by openssl. These
         * values MUST be decoded first before using them.
         * @return array Returns the keys Base64-encoded for easy storage
         * @see openssl_get_cipher_methods()
         */
        public static function cipherKeys(string $algorithm = 'aes-256-cbc'): array
        {
            $keyLen = openssl_cipher_key_length($algorithm);
            $ivLen = openssl_cipher_iv_length($algorithm);
            return [
                'password' => base64_encode(openssl_random_pseudo_bytes($keyLen)),
                'initVector' => base64_encode(openssl_random_pseudo_bytes($ivLen))
            ];
        }

        /**
         * Generates a random unique value for using in symmetric digital signature
         * @param int $length Byte length
         * @return string Encodes output using base 64 format for easier storage.
         */
        public static function signature(int $length = 32): string
        {
            return base64_encode(random_bytes($length));
        }

        /**
         * Generates asymmetric key pairs (Private & Public keys). Writes the keys
         * to the specified directory, ensuring structured storage.
         * @param array $configs
         * @param string $destination Destination directory
         * @param string $prefix Prefix
         * @return bool
         * @throws \Exception
         */
        private static function generate(array $configs, string $destination, string $prefix): bool
        {
            if (is_writable($destination) || mkdir($destination, 0600, true)) {
                $filename = $destination . '/' . date('Y-m-d') . '_' . $prefix;
                $res = openssl_pkey_new($configs);
                openssl_pkey_export($res, $privateKey);
                file_put_contents($filename . '_private.pem', $privateKey);
                $publicKey = openssl_pkey_get_details($res)['key'];
                openssl_free_key($res); //free memory
                return file_put_contents($filename . '_public.pem', $publicKey) !== false;
            }
            throw new \Exception('Destination directory is not writable.');
        }

        /**
         * Built for modern cryptographic needs, balancing speed, security, and simplicity.
         * @param string $destination Destination directory where keys will be saved.
         * If keys exists, they will be overwritten
         * @return bool True on success, false otherwise
         */
        public static function ed25519(string $destination): bool
        {
            if (!defined('OPENSSL_KEYTYPE_ED25519')) {
                define('OPENSSL_KEYTYPE_ED25519', 5); // 5 is the expected internal value for Ed25519
            }
            $configs = ['private_key_type' => OPENSSL_KEYTYPE_ED25519];
            return self::generate($configs, $destination, 'ed25519');
        }

        /**
         * @param string $destination Destination directory where keys will be saved.
         * If keys exists, they will be overwritten.
         * @param int $keySize Key size. Must be a multiple of 128, higher value
         * means more secure but slower.
         * @param string $algorithm The hashing algorithm used (e.g., SHA256, SHA512, etc.).
         * @return bool True on success, false otherwise
         */
        public static function rsa(string $destination, int $keySize = 2048, string $algorithm = 'sha256'): string
        {
            $configs = [
                'digest_alg' => $algorithm,
                'private_key_bits' => $keySize,
                'private_key_type' => OPENSSL_KEYTYPE_RSA
            ];
            return self::generate($configs, $destination, 'rsa');
        }

        /**
         * @param string $destination Destination directory where keys will be saved.
         * If keys exists, they will be overwritten
         * @return bool True on success, false otherwise
         */
        public static function ecdsa(string $destination, string $curveName = 'prime256v1'): string
        {
            $configs = ['curve_name' => $curveName]; // Most secure curve
            return self::generate($configs, $destination, 'ecdsa');
        }
    }

}
