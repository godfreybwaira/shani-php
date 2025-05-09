<?php

/**
 * Description of CounterBasedOTP
 * @author coder
 *
 * Created on: May 9, 2025 at 2:19:41 PM
 */

namespace lib\crypto\otp {

    final class CounterBasedOTP implements \Stringable
    {

        /**
         * The HMAC algorithm (default 'SHA1')
         * @var string
         */
        private readonly string $algorithm;

        /**
         * A shared secret key in raw binary form. If you have a Base32 secret,
         * you'll need to decode it first.
         * @var string
         */
        private readonly string $password;

        /**
         * The user's account name or email.
         * @var string
         */
        private readonly ?string $accountName;

        /**
         * The issuer or organization name.
         * @var string
         */
        private readonly ?string $issuer;

        /**
         * The number of OTP digits (default 6)
         * @var int
         */
        private readonly int $length;

        /**
         * The starting counter for HOTP.
         * @var int
         */
        private readonly int $counter;

        /**
         * Generates a Count-based one time password (HOTP)
         * @param string $password A shared secret key in raw binary form. If you
         * have a Base32 secret, you'll need to decode it first.
         * @param int $counter The starting counter for HOTP.
         * @param int $length The number of OTP digits (default 6).
         * @param string $issuer The issuer or organization name.
         * @param string $accountName The user's account name or email.
         */
        public function __construct(string $password, int $counter, int $length = 6, string $issuer = null, string $accountName = null)
        {
            $this->password = $password;
            $this->length = $length;
            $this->algorithm = 'SHA1';
            $this->counter = $counter;
            $this->accountName = $accountName;
            $this->issuer = $issuer;
        }

        /**
         * Generates a Counter-based One-Time Password (HOTP) as per RFC 4226.
         * @return string The generated OTP code as a zero-padded string.
         */
        public function generate()
        {
            // Convert the counter to an 8-byte (64-bit) big-endian binary string.
            $counterBytes = pack('N*', 0, $this->counter);
            $hash = hash_hmac($this->algorithm, $counterBytes, $this->password, true);
            // Dynamic truncation:
            // Use the lower 4 bits of the last byte of $hash as an offset.
            $offset = ord(substr($hash, -1)) & 0x0F;
            // Extract 4 bytes from the hash starting at the offset.
            // Use bitwise operations to extract a 31-bit positive integer.
            $binaryCode = (
                    ((ord($hash[$offset]) & 0x7f) << 24) |
                    ((ord($hash[$offset + 1]) & 0xff) << 16) |
                    ((ord($hash[$offset + 2]) & 0xff) << 8) |
                    (ord($hash[$offset + 3]) & 0xff)
            );
            $otp = $binaryCode % pow(10, $this->length);
            // Pad with leading zeros to ensure the OTP has the desired length.
            return str_pad($otp, $this->length, '0', STR_PAD_LEFT);
        }

        /**
         * Check if generated OTP code matches with the existing one
         * @param int $otp Existing OTP
         * @return bool Returns true on success, false otherwise
         */
        public function verify(int $otp): bool
        {
            $newOtp = new self($this->password, $this->counter, strlen($otp));
            return $newOtp->generate() === $otp;
        }

        #[\Override]
        public function __toString(): string
        {
            $label = urlencode($this->issuer . ':' . $this->accountName);
            // Build the query string with necessary parameters.
            $queryParams = http_build_query([
                'secret' => $this->password,
                'issuer' => urlencode($this->issuer),
                'algorithm' => $this->algorithm,
                'digits' => $this->length,
                'counter' => $this->counter
            ]);
            return "otpauth://hotp/{$label}?{$queryParams}";
        }
    }

}
