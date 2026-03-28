<?php

/**
 * Description of JWTClaim
 * @author goddy
 *
 * Created on: Mar 26, 2026 at 12:17:10 PM
 */

namespace lib\jwt {

    use lib\ds\map\ReadableMap;
    use lib\jwt\exceptions\JWTAlgorithmException;
    use lib\jwt\exceptions\JWTFormatException;
    use lib\jwt\exceptions\JWTSignatureException;
    use lib\oauth2\PKCEGenerator;
    use lib\URI;

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
            // 1. Create the Header
            $header = json_encode(['typ' => 'JWT', 'alg' => $algorithm->name]);
            $base64Header = self::base64UrlEncode($header);

            // 2. Create the Payload
            $base64Payload = self::base64UrlEncode(json_encode($this));

            // 3. Create the Signature
            $signature = hash_hmac($algorithm->value, $base64Header . '.' . $base64Payload, $secretKey, true);
            $base64Signature = self::base64UrlEncode($signature);

            // 4. Combine them all
            return $base64Header . '.' . $base64Payload . '.' . $base64Signature;
        }

        /**
         * Create a JWT Claim object from a valid JWT token
         * @param string $token JWT token string
         * @param string $secretKey Secret key for signing a JWT token
         * @param JWTAlgorithm $algorithm JWT signature algorithm
         * @return JWTClaim JWTClaim object
         * @throws JWTFormatException
         * @throws JWTAlgorithmException
         * @throws JWTSignatureException
         * @throws \Exception
         */
        public static function createFromToken(string $token, string $secretKey, JWTAlgorithm $algorithm = JWTAlgorithm::HS256): JWTClaim
        {
            $tokenParts = explode('.', $token);
            if (count($tokenParts) !== 3) {
                throw new JWTFormatException('Invalid JWT token format.');
            }
            list($headerEncoded, $payloadEncoded, $signatureEncoded) = $tokenParts;
            $header = json_decode(self::base64UrlDecode($headerEncoded), true);
            $payload = json_decode(self::base64UrlDecode($payloadEncoded), true);
            if (!is_array($header) || !is_array($payload)) {
                throw new \Exception('Malformed UTF-8 characters or invalid JSON inside the token.');
            }
            if (empty($header['alg']) || $header['alg'] !== $algorithm->name) {
                throw new JWTAlgorithmException('Token algorithm does not match the expected algorithm.');
            }
            $signature = hash_hmac($algorithm->value, $headerEncoded . '.' . $payloadEncoded, $secretKey, true);
            $expiresIn = $payload['exp'] ?? 0;
            $issuedAt = $payload['iat'] ?? 0;
            if (hash_equals(self::base64UrlEncode($signature), $signatureEncoded)) {
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
            throw new JWTSignatureException('Signature validation failed.');
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
