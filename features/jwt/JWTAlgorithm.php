<?php

/**
 * Defines the supported signing algorithms for JSON Web Tokens.
 * This ensures type safety and prevents attackers from switching
 * between symmetric and asymmetric signing methods.
 *
 * @author goddy
 *
 * Created on: Mar 26, 2026 at 12:04:43 PM
 */

namespace features\jwt {

    enum JWTAlgorithm
    {

        ///////HMAC: Symmetric Algorithms
        case HS256; //Standard / Fast
        case HS384;
        case HS512;
        ////////RSA:
        case RS256;
        case RS384;
        case RS512;
        ///////ECDSA: Modern / Small Keys
        case ES256;
        case ES384;
        case ES512;
        /////ED25519: Modern, Fastest, smallest keys
        case EdDSA;

        public function getValue(): ?string
        {
            if ($this === self::EdDSA) {
                return null;
            }
            return 'sha' . substr($this->name, 2);
        }

        /**
         * Determines if the algorithm uses a public/private key pair.
         * @return bool True if symmetric, false if asymmetric.
         */
        public function isSymmetric(): bool
        {
            return str_starts_with($this->name, 'HS');
        }

        /**
         * Determines if the algorithm is based on Elliptic Curve Cryptography.
         * @return bool
         */
        public function isEllipticCurve(): bool
        {
            return str_starts_with($this->name, 'ES');
        }
    }

}
