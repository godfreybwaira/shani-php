<?php

/**
 * Generates and validates PKCE (Proof Key for Code Exchange) values as defined in RFC 7636.
 *
 * This class produces cryptographically secure code verifiers and S256 code challenges,
 * which are required for secure OAuth 2.0 Authorization Code flow in public clients
 * (mobile, SPA, desktop apps, etc.).
 *
 * @author goddy
 *
 * Created on: Mar 19, 2026 at 4:26:30 PM
 */

namespace lib\oauth2 {

    final class PKCEGenerator
    {

        /**
         * @var string The high-entropy code verifier (43–128 characters,
         * base64url-encoded random bytes). This value must be kept secret and
         * sent only in the token request.
         */
        public readonly string $codeVerifier;

        /**
         * @var string The code challenge method used. Always "S256" in modern
         * implementations (SHA-256 + base64url). Servers MUST support S256 per RFC 7636 § 4.2.
         */
        public readonly string $codeChallengeMethod;

        /**
         * @var string The derived code challenge sent in the authorization request.
         * Computed as: BASE64URL-ENCODE(SHA256(ASCII(code_verifier)))
         */
        public readonly string $codeChallenge;

        public const BAD_CHARS = ['+', '/', '='];
        public const GOOD_CHARS = ['-', '_', ''];

        /**
         * @param int $length Number of random bytes to generate (32–96 is recommended)
         * results in verifier length ≈ 43–128 chars after encoding
         */
        public function __construct(int $length = 72)
        {
            $randomBytes = random_bytes($length);
            $this->codeVerifier = str_replace(self::BAD_CHARS, self::GOOD_CHARS, base64_encode($randomBytes));
            $hash = hash('sha256', $this->codeVerifier, true);
            $this->codeChallenge = str_replace(self::BAD_CHARS, self::GOOD_CHARS, base64_encode($hash));
            $this->codeChallengeMethod = 'S256';
        }

        /**
         * Validates a received code challenge against the original verifier.
         *
         * Use this server-side (or in tests) when you want to verify PKCE logic.
         * In real OAuth flows, the authorization server performs this check.
         *
         * @param string $challenge       The code_challenge value received
         * @param string $challengeMethod The code_challenge_method received
         * @param string $codeVerifier    The original code verifier
         *
         * @return bool True if the challenge matches the verifier using the given method
         */
        public static function validatePKCE(string $challenge, string $challengeMethod, string $codeVerifier): bool
        {
            if ($challengeMethod === 'S256') {
                $computedHash = hash('sha256', $codeVerifier, true);
                $computedChallenge = str_replace(self::BAD_CHARS, self::GOOD_CHARS, base64_encode($computedHash));
                return hash_equals($challenge, $computedChallenge);
            }
            return false;
        }
    }

}
