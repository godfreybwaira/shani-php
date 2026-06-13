<?php

namespace features\crypto {

    /**
     * Interface DigitalSignature
     *
     * Provides methods for creating and verifying digital signatures
     * to ensure data integrity and authenticity.
     *
     * Typical implementations may rely on asymmetric cryptography
     * (private/public key pairs) or password-based signing mechanisms.
     *
     * @author coder
     * @since Apr 28, 2025 at 11:08:54 AM
     */
    interface DigitalSignature
    {

        /**
         * Generate a digital signature for the given payload.
         *
         * The signature is created using a private key or password-based
         * mechanism depending on the implementation.
         *
         * @param string $payload The raw data to be signed.
         * @return string The generated signature representing the signed payload.
         *
         * @throws \Exception If signing fails due to invalid key or configuration.
         */
        public function sign(string $payload): string;

        /**
         * Verify the authenticity of a signed payload.
         *
         * This method checks whether the provided signature matches the
         * expected signature for the given payload using a public key or
         * other verification mechanism.
         *
         * @param string $payload   The original unsigned payload to verify.
         * @param string|null $signature The signature to validate against the payload.
         *
         * @return bool True if the signature is valid and matches the payload.
         *
         * @throws \Exception If the signature is missing, empty, or invalid.
         */
        public function verify(string $payload, ?string $signature): bool;
    }

}
