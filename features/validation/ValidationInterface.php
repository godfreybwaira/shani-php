<?php

namespace features\validation {

    /**
     * ValidationInterface
     *
     * Defines the contract for validation logic applied to request data or files.
     * Implementations should validate a given key/value pair and return either
     * a ValidationError object describing the failure, or null if validation passes.
     *
     * This interface is designed to be used by validators such as MultiFileValidator
     * or RequestBodyValidator to enforce consistent validation rules.
     *
     * @author goddy
     * @created May 23, 2026 at 2:13:04 PM
     */
    interface ValidationInterface
    {

        /**
         * Validates a given key/value pair.
         *
         * @param string|int $key   The identifier of the field or file being validated.
         * @param mixed      $value The value to validate (e.g., request body field, file object).
         *
         * @return ValidationError|null Returns a ValidationError if validation fails,
         *                              or null if validation passes successfully.
         */
        public function validate(string|int $key, mixed $value): ?ValidationError;
    }

}
