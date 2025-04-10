<?php

/**
 * Description of Password
 * @author coder
 *
 * Created on: Jan 28, 2021 at 6:26:43 PM
 */

namespace lib\validation {

    final class PasswordComplexity
    {

        public readonly int $minLength, $maxLength;
        public readonly bool $digits, $upperCase, $lowerCase, $symbols;

        public function __construct(
                int $minLength = 6, int $maxLength = 20, bool $digits = true,
                bool $upperCase = false, bool $lowerCase = true, bool $symbols = true
        )
        {

            $this->minLength = $minLength;
            $this->maxLength = $maxLength;
            $this->digits = $digits;
            $this->upperCase = $upperCase;
            $this->lowerCase = $lowerCase;
            $this->symbols = $symbols;
        }
    }

}
