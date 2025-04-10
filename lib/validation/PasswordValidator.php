<?php

/**
 * Description of Password
 * @author coder
 *
 * Created on: Jan 28, 2021 at 2:20:20 PM
 */

namespace lib\validation {

    final class PasswordValidator
    {

        private string $password;

        public function __construct(string $password)
        {
            $this->password = $password;
        }

        public function validate(PasswordComplexity $complexity): bool
        {
            $expression = null;
            if ($complexity->digits) {
                $expression .= '(?=.*[0-9])';
            }
            if ($complexity->lowerCase) {
                $expression .= '(?=.*[a-z])';
            }
            if ($complexity->upperCase) {
                $expression .= '(?=.*[A-Z])';
            }
            if ($complexity->symbols) {
                $expression .= '(?=.*[)!@#$%^&*,.+=(?_])';
            }
            return preg_match('/^' . $expression . '.{' . $complexity->minLength . ',' . $complexity->maxLength . '}$/', $this->password) === 1;
        }

        public function hasDigits(int $min = 1, int $max = null): bool
        {
            return preg_match('/[0-9]{' . $min . ',' . $max . '}/', $this->password) === 1;
        }

        public function hasLowercase(int $min = 1, int $max = null): bool
        {
            return preg_match('/[a-z]{' . $min . ',' . $max . '}/', $this->password) === 1;
        }

        public function hasUppercase(int $min = 1, int $max = null): bool
        {
            return preg_match('/[A-Z]{' . $min . ',' . $max . '}/', $this->password) === 1;
        }

        public function hasSymbols(int $min = 1, int $max = null): bool
        {
            return preg_match('/[\)!@#$%^&*,.+=\(?_]{' . $min . ',' . $max . '}/', $this->password) === 1;
        }
    }

}
