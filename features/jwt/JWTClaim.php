<?php

namespace features\jwt {

    use features\jwt\exceptions\JWTAlgorithmException;
    use features\jwt\exceptions\JWTExpirationException;
    use features\jwt\exceptions\JWTFormatException;
    use features\jwt\exceptions\JWTSignatureException;
    use features\oauth2\PKCEGenerator;
    use features\utils\Duration;

    /**
     * Class JWTClaim
     *
     * Represents a set of claims for a JSON Web Token (JWT).
     * Provides methods to set standard claims (iss, sub, aud, exp, nbf, iat, jti),
     * add custom claims, generate signed JWT tokens, and validate existing tokens.
     *
     * @package features\jwt
     * @author goddy
     * @created Mar 26, 2026 at 12:17:10 PM
     */
    final class JWTClaim
    {

        /**
         * @var array<string, mixed> Claims stored in the JWT payload.
         */
        private array $claims = [];

        /**
         * @var JWTAlgorithm Algorithm used for signing/verifying JWT.
         */
        private readonly JWTAlgorithm $algorithm;

        /**
         * Construct a new JWTClaim instance.
         * Initializes default claims: issued-at (iat), not-before (nbf),
         * expiration (exp, default 15 minutes), and unique ID (jti).
         *
         * @param JWTAlgorithm $algorithm JWT signature algorithm (default HS256).
         */
        public function __construct(JWTAlgorithm $algorithm = JWTAlgorithm::HS256)
        {
            $this->algorithm = $algorithm;
            $now = new \DateTimeImmutable();
            $this->setIssuedAt($now)->setNotBefore($now);
            $duration = Duration::ofMinutes(15);
            $this->setExpire($duration)->setId(bin2hex(random_bytes(8)));
        }

        /**
         * Set the JWT ID (jti).
         *
         * @param string $jwtId Unique identifier for the token.
         * @return JWTClaim
         */
        public function setId(string $jwtId): JWTClaim
        {
            $this->claims['jti'] = $jwtId;
            return $this;
        }

        /**
         * Set the subject (sub).
         *
         * @param string $subject Subject of the token (e.g., user ID).
         * @return JWTClaim
         */
        public function setSubject(string $subject): JWTClaim
        {
            $this->claims['sub'] = $subject;
            return $this;
        }

        /**
         * Set the issuer (iss).
         *
         * @param string $issuer Issuer of the token.
         * @return JWTClaim
         */
        public function setIssuer(string $issuer): JWTClaim
        {
            $this->claims['iss'] = $issuer;
            return $this;
        }

        /**
         * Set the audience (aud).
         *
         * @param array|string $audience Audience(s) for the token.
         * @return JWTClaim
         */
        public function setAudience(array|string $audience): JWTClaim
        {
            $this->claims['aud'] = $audience;
            return $this;
        }

        /**
         * Set the expiration time (exp).
         *
         * @param Duration $ttl Time-to-live duration.
         * @return JWTClaim
         */
        public function setExpire(Duration $ttl): JWTClaim
        {
            $this->claims['exp'] = $ttl->toDateTime()->getTimestamp();
            return $this;
        }

        /**
         * Set the not-before time (nbf).
         *
         * @param \DateTimeInterface $notBefore Time before which token is invalid.
         * @return JWTClaim
         */
        public function setNotBefore(\DateTimeInterface $notBefore): JWTClaim
        {
            $this->claims['nbf'] = $notBefore->getTimestamp();
            return $this;
        }

        /**
         * Set the issued-at time (iat).
         *
         * @param \DateTimeInterface $issuedAt Time when token was issued.
         * @return JWTClaim
         */
        public function setIssuedAt(\DateTimeInterface $issuedAt): JWTClaim
        {
            $this->claims['iat'] = $issuedAt->getTimestamp();
            return $this;
        }

        /**
         * Set a custom claim.
         *
         * @param string $key Claim name.
         * @param mixed $value Claim value.
         * @return JWTClaim
         */
        public function setClaim(string $key, mixed $value): JWTClaim
        {
            $this->claims[$key] = $value;
            return $this;
        }

        /**
         * Retrieve a claim value.
         *
         * @param string $key Claim name.
         * @param mixed|null $default Default value if claim not found.
         * @return mixed Claim value or default.
         */
        public function getClaim(string $key, mixed $default = null): mixed
        {
            return $this->claims[$key] ?? $default;
        }

        /**
         * Check if the token is valid (not expired and not-before satisfied).
         *
         * @return bool True if valid, false otherwise.
         */
        public function isValid(): bool
        {
            $moment = (new \DateTimeImmutable())->getTimestamp();
            return ($this->claims['exp'] ?? 0) >= $moment && $moment >= ($this->claims['nbf'] ?? 0);
        }

        /**
         * Get remaining time-to-live in seconds.
         *
         * @return int Remaining seconds before expiration.
         */
        public function getRemainingTtl(): int
        {
            return max(0, ($this->claims['exp'] ?? 0) - (new \DateTimeImmutable())->getTimestamp());
        }

        /**
         * Generate a signed JWT token string from claims.
         *
         * @param string $secretKey Secret key for signing.
         * @return string JWT token string.
         * @throws JWTFormatException If signature generation fails.
         */
        public function getToken(string $secretKey): string
        {
            $header = ['typ' => 'JWT', 'alg' => $this->algorithm->name];
            $segments = [
                self::base64UrlEncode(json_encode($header)),
                self::base64UrlEncode(json_encode($this->claims))
            ];

            $dataToSign = implode('.', $segments);
            $signature = null;
            if ($this->algorithm->isSymmetric()) {
                $signature = hash_hmac($this->algorithm->getValue(), $dataToSign, $secretKey, true);
            } else {
                openssl_sign($dataToSign, $signature, $secretKey, $this->algorithm->getValue());
                if ($this->algorithm->isEllipticCurve()) {
                    $signature = ECDSAHelper::der2Sig($signature);
                }
            }
            if (!empty($signature)) {
                $segments[] = self::base64UrlEncode($signature);
                return implode('.', $segments);
            }
            throw new JWTFormatException('Invalid JWT signature');
        }

        /**
         * Parse and validate a JWT token string into a JWTClaim object.
         *
         * @param string $token JWT token string.
         * @param string $verificationKey Key for verifying signature.
         * @param JWTAlgorithm $algorithm Expected algorithm.
         * @return JWTClaim Parsed JWTClaim object.
         * @throws JWTFormatException If token format is invalid.
         * @throws JWTAlgorithmException If algorithm mismatch occurs.
         * @throws JWTSignatureException If signature verification fails.
         * @throws JWTExpirationException If token is expired.
         */
        public static function fromToken(string $token, string $verificationKey, JWTAlgorithm $algorithm): JWTClaim
        {
            $parts = explode('.', $token);
            if (count($parts) !== 3) {
                throw new JWTFormatException('Invalid token structure');
            }
            list($headB64, $payloadB64, $sigB64) = $parts;
            $dataToVerify = $headB64 . '.' . $payloadB64;
            $signature = self::base64UrlDecode($sigB64);
            $header = json_decode(self::base64UrlDecode($headB64), true);
            if (empty($header['alg']) || $header['alg'] !== $algorithm->name) {
                throw new JWTAlgorithmException('Token algorithm does not match the expected algorithm.');
            }
            if ($algorithm->isSymmetric()) {
                $expected = hash_hmac($algorithm->getValue(), $dataToVerify, $verificationKey, true);
                if (!hash_equals($expected, $signature)) {
                    throw new JWTSignatureException('Signature Verification failed.');
                }
            } else {
                // ES256 Fix: Convert Raw R+S back to DER for OpenSSL
                if ($algorithm->isEllipticCurve()) {
                    $signature = ECDSAHelper::sig2Der($signature);
                }
                $result = openssl_verify($dataToVerify, $signature, $verificationKey, $algorithm->getValue());
                if ($result !== 1) {
                    throw new JWTSignatureException('Signature Verification failed.');
                }
            }
            $payload = json_decode(self::base64UrlDecode($payloadB64), true);
            if (Duration::expired($payload['exp'] ?? 0)) {
                throw new JWTExpirationException('Token expired');
            }
            return self::payload2Claim($algorithm, $payload);
        }

        /**
         * Convert payload array into a JWTClaim object.
         *
         * @param JWTAlgorithm $algorithm Algorithm used
         * @param array<string, mixed> $payload Token payload
         * @return JWTClaim
         */
        private static function payload2Claim(JWTAlgorithm $algorithm, array $payload): JWTClaim
        {
            $jwt = new JWTClaim($algorithm);
            foreach ($payload as $key => $value) {
                $jwt->setClaim($key, $value);
            }
            if (!empty($payload['iat'])) {
                if (!empty($payload['exp'])) {
                    $jwt->setExpire(Duration::ofSeconds($payload['exp'] - $payload['iat']));
                }
                $jwt->setIssuedAt(Duration::fromTimestamp($payload['iat'])->toDateTime());
            }
            if (!empty($payload['nbf'])) {
                $jwt->setNotBefore(Duration::fromTimestamp($payload['nbf'])->toDateTime());
            }
            return $jwt;
        }

        /**
         * Encode data using URL-safe Base64.
         *
         * @param string $data Raw data
         * @return string URL-safe Base64 encoded string
         */
        private static function base64UrlEncode(string $data): string
        {
            return str_replace(PKCEGenerator::BAD_CHARS, PKCEGenerator::GOOD_CHARS, base64_encode($data));
        }

        /**
         * Decode a URL-safe Base64 string.
         *
         * @param string $data Encoded data
         * @return string Decoded raw data
         */
        private static function base64UrlDecode(string $data): string
        {
            $remainder = strlen($data) % 4;
            if ($remainder) {
                $padlen = 4 - $remainder;
                $data .= str_repeat('=', $padlen);
            }
            return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
        }
    }

}
