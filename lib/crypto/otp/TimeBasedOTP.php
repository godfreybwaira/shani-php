<?php

/**
 * Description of TimeBasedOTP
 * @author coder
 *
 * Created on: May 9, 2025 at 1:19:30 PM
 */

namespace lib\crypto\otp {

    final class TimeBasedOTP implements \Stringable
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
         * The time step in seconds (default 30 seconds)
         * @var int
         */
        private readonly int $period;

        /**
         * Generates a Time-based one time password (TOTP)
         * @param string $password A shared secret key in raw binary form. If you
         * have a Base32 secret, you'll need to decode it first.
         * @param int $length The number of OTP digits (default 6).
         * @param string $issuer The issuer or organization name.
         * @param string $accountName The user's account name or email.
         */
        public function __construct(string $password, int $length = 6, string $issuer = null, string $accountName = null)
        {

            $this->password = $password;
            $this->length = $length;
            $this->algorithm = 'SHA1';
            $this->period = 30;
            $this->accountName = $accountName;
            $this->issuer = $issuer;
        }

        /**
         * Generate OTP code
         * @return int OTP code
         */
        public function generate(): int
        {
            $timeSlice = floor(time() / $this->period);
            // Pack time interval as a binary string of 8 bytes (big endian)
            $time = pack('N*', 0) . pack('N*', $timeSlice);

            // HMAC-SHA1 hashing
            $hash = hash_hmac($this->algorithm, $time, $this->password, true);
            // Dynamic truncation to extract a 4-byte string
            $offset = ord(substr($hash, -1)) & 0x0F;
            $part = substr($hash, $offset, 4);
            $value = unpack('N', $part)[1] & 0x7FFFFFFF;
            // Generate code
            return str_pad($value % pow(10, $this->length), $this->length, '0', STR_PAD_LEFT);
        }

        /**
         * Check if generated OTP code matches with the existing one
         * @param int $otp Existing OTP
         * @return bool Returns true on success, false otherwise
         */
        public function verify(int $otp): bool
        {
            $newOtp = new self($this->password, strlen($otp));
            return $newOtp->generate() === $otp;
        }

        #[\Override]
        public function __toString(): string
        {
            $label = urlencode($this->issuer . ':' . $this->accountName);
            $queryParams = http_build_query([
                'secret' => $this->password,
                'issuer' => urlencode($this->issuer),
                'algorithm' => $this->algorithm,
                'digits' => $this->length,
                'period' => $this->period,
            ]);
            // Create the full URI.
            return "otpauth://totp/{$label}?{$queryParams}";
        }
    }

}
