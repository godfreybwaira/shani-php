<?php

/**
 * OTP password generator
 * @author coder
 *
 * Created on: Apr 28, 2025 at 9:57:48 AM
 */

namespace features\otp {

    use features\utils\DataConvertor;

    final class OTP
    {

        /**
         * Generate a unique random OTP password
         * @param int $length Length of a password
         * @return string OTP password
         */
        public static function password(int $length = 32): string
        {
            $chars = null;
            $charsLength = strlen(DataConvertor::BASE32_CHARS);
            for ($i = 0; $i < $length; $i++) {
                $chars .= DataConvertor::BASE32_CHARS[random_int(0, $charsLength - 1)];
            }
            return $chars;
        }
    }

}
