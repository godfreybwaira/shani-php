<?php

/**
 * Description of Password
 * @author coder
 *
 * Created on: Jan 28, 2021 at 6:26:43 PM
 */

namespace lib\validation {

    interface PasswordComplexity {

        public const MIN_LENGTH = 8;
        public const MAX_LENGTH = 20; //can be > 0 or NULL
        public const DIGITS = false;
        public const UPPER_CASE = false;
        public const LOWER_CASE = false;
        public const LETTERS = true;
        public const SYMBOLS = false;
        public const RESET_VALIDITY = 15; //minutes
    }

}
