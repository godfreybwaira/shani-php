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

namespace lib\jwt {

    enum JWTAlgorithm: string
    {

        case HS256 = 'sha256'; //Standard / Fast
        case HS384 = 'sha384';
        case HS512 = 'sha512';
        case RS256 = 'sha256';
        case RS384 = 'sha384';
        case RS512 = 'sha512';
        case ES256 = 'sha256'; //ECDSA: Modern / Small Keys

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
            return $this === self::ES256;
        }
    }

}
