<?php

/**
 * Description of DigitalSignature
 * @author coder
 *
 * Created on: Apr 28, 2025 at 11:08:54 AM
 */

namespace lib\crypto {

    interface DigitalSignature
    {

        /**
         * Signs the provided payload using private key or password
         * @param string $payload
         * @return string Returns a signature represents a signed payload
         */
        public function sign(string $payload): string;

        /**
         * Verify the authenticity of the signed data using public key or known
         * signature. Throws an exception if: The signature is empty or invalid
         * @param string $payload Unsigned payload to verify
         * @param string|null $signature The previous signature used to sign a payload
         * @return bool Return true if the signature is valid
         */
        public function verify(string $payload, ?string $signature): bool;
    }

}
