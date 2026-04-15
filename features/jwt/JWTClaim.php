<?php

/**
 * Description of JWTClaim
 * @author goddy
 *
 * Created on: Mar 26, 2026 at 12:17:10 PM
 */

namespace features\jwt {

    use features\ds\map\ReadableMap;
    use features\jwt\exceptions\JWTAlgorithmException;
    use features\jwt\exceptions\JWTFormatException;
    use features\jwt\exceptions\JWTSignatureException;
    use features\oauth2\PKCEGenerator;
    use features\utils\URI;

    final class JWTClaim implements \JsonSerializable
    {

        /**
         * Identifies the principal that issued, and sign (if applicable) the JWT.
         * @var URI
         */
        public readonly URI $issuer;

        /**
         * Unique identifier for the JWT (useful for preventing replay attacks).
         * MUST be unique with negligible collision probability
         * @var string
         */
        public readonly string $jwtId;

        /**
         * Time before which the JWT MUST NOT be accepted. Current time MUST be
         * after or equal to this value
         * @var \DateTimeInterface
         */
        public readonly \DateTimeInterface $notBefore;

        /**
         * Time at which the JWT was issued. Can be used to calculate token age
         * @var \DateTimeInterface
         */
        public readonly \DateTimeInterface $issuedAt;

        /**
         * Identifies the principal that is the subject of the JWT. MUST be locally
         * or globally unique in issuer context. Example: "userId123"
         * @var string|null
         */
        public readonly ?string $subject;

        /**
         * Identifies the recipients that the JWT is intended for. If present,
         * processor MUST match one of the values or reject the JWT
         * @var array
         */
        public readonly array $audience;

        /**
         * Time after which the JWT MUST NOT be accepted. Current time MUST be
         * before exp (small leeway allowed)
         * @var \DateTimeInterface
         */
        public readonly \DateTimeInterface $expiresIn;

        /**
         * JWT data
         * @var ReadableMap|null
         */
        public readonly ?ReadableMap $payload;

        /**
         * JWT parts
         */
        public const LENGTH = 3;

        public function __construct(
                URI $issuer, ?ReadableMap $payload = null, ?string $subject = null,
                array $audience = [], int $ttl = 3600, ?string $jwtId = null,
                ?\DateTimeInterface $issueAt = null,
                ?\DateTimeInterface $notBefore = null
        )
        {
            $this->issuedAt = $issueAt ?? new \DateTimeImmutable();
            $this->jwtId = $jwtId ?? bin2hex(random_bytes(16));
            $this->expiresIn = \DateTimeImmutable::createFromFormat('U', $this->issuedAt->getTimestamp() + $ttl);
            $this->notBefore = $notBefore ?? $this->issuedAt;
            $this->issuer = $issuer;
            $this->subject = $subject;
            $this->audience = $audience;
            $this->payload = $payload;
        }

        #[\Override]
        public function jsonSerialize(): array
        {
            return [
                'jti' => $this->jwtId,
                'sub' => $this->subject,
                'iss' => $this->issuer->asString(),
                'aud' => $this->audience,
                'exp' => $this->expiresIn->getTimestamp(),
                'nbf' => $this->notBefore->getTimestamp(),
                'iat' => $this->issuedAt->getTimestamp(),
                'payload' => $this->payload
            ];
        }

        /**
         * Tells whether a JWT token is still valid i.e not expired and it is ready for use.
         * @return bool True if the token is valid, false otherwise
         */
        public function isValid(): bool
        {
            $now = time();
            return $this->expiresIn->getTimestamp() >= $now && $now >= $this->notBefore->getTimestamp();
        }

        /**
         * Get the remaining time (seconds) before token has expired.
         * @return int
         */
        public function getRemainingTtl(): int
        {
            return max(0, $this->expiresIn->getTimestamp() - time());
        }

        /**
         * Convert a JWT claim object to a valid JWT token string.
         * @param string $secretKey Secret key for signing a JWT token
         * @param JWTAlgorithm $algorithm JWT signature algorithm
         * @return string JWT token string
         */
        public function asToken(string $secretKey, JWTAlgorithm $algorithm = JWTAlgorithm::HS256): string
        {
            $header = ['typ' => 'JWT', 'alg' => $algorithm->name];
            $segments = [
                self::base64UrlEncode(json_encode($header)),
                self::base64UrlEncode(json_encode($this))
            ];

            $dataToSign = implode('.', $segments);
            $signature = null;
            if ($algorithm->isSymmetric()) {
                $signature = hash_hmac($algorithm->getValue(), $dataToSign, $secretKey, true);
            } else {
                openssl_sign($dataToSign, $signature, $secretKey, $algorithm->getValue());
                // ES256 Fix: Convert DER signature to Raw R+S
                if ($algorithm->isEllipticCurve()) {
                    $signature = ECDSAHelper::der2Sig($signature);
                }
            }
            $segments[] = self::base64UrlEncode($signature);
            return implode('.', $segments);
        }

        /**
         * Create a JWT Claim object from a valid JWT token
         * @param string $token JWT token string
         * @param string $verificationKey Key for verifying a JWT signature. For
         * Asymmetric algorithms, it is the public key, for Symmetric algorithms,
         * it is the same key used to sign a JWT
         * @param JWTAlgorithm $algorithm JWT signature algorithm
         * @return JWTClaim JWTClaim object
         * @throws JWTFormatException
         * @throws JWTAlgorithmException
         * @throws JWTSignatureException
         */
        public static function fromToken(string $token, string $verificationKey, JWTAlgorithm $algorithm = JWTAlgorithm::HS256): JWTClaim
        {
            $parts = explode('.', $token);
            if (count($parts) !== JWTClaim::LENGTH) {
                throw new JWTFormatException('Invalid token structure.');
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
            return self::payload2Claim($payload);
        }

        private static function payload2Claim(array $payload): JWTClaim
        {
            $expiresIn = $payload['exp'] ?? 0;
            $issuedAt = $payload['iat'] ?? 0;
            return new JWTClaim(
                    new URI($payload['iss']),
                    !empty($payload['payload']) ? new ReadableMap($payload['payload']) : null,
                    $payload['sub'] ?? null,
                    (array) ($payload['aud'] ?? []),
                    $expiresIn - $issuedAt,
                    $payload['jti'] ?? null,
                    !empty($payload['iat']) ? \DateTimeImmutable::createFromFormat('U', $payload['iat']) : null,
                    !empty($payload['nbf']) ? \DateTimeImmutable::createFromFormat('U', $payload['nbf']) : null
            );
        }

        /**
         * Standard Base64 encoding isn't URL-safe.
         * We need to replace +, /, and remove padding =.
         */
        private static function base64UrlEncode(string $data): string
        {
            return str_replace(PKCEGenerator::BAD_CHARS, PKCEGenerator::GOOD_CHARS, base64_encode($data));
        }

        /**
         * Decodes a Base64URL string back to original data.
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
