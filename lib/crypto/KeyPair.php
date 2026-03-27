<?php

/**
 * Asymmetric key-pair class
 * @author goddy
 *
 * Created on: Mar 27, 2026 at 6:00:45 PM
 */

namespace lib\crypto {

    final class KeyPair
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
         * @param string $privateKey Used for decrypting payloads.
         * @param string $publicKey Used for encrypting payloads.
         */
        public function __construct(string $privateKey, string $publicKey)
        {
            $this->privateKey = $privateKey;
            $this->publicKey = $publicKey;
        }
    }

}
