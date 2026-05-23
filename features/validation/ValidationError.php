<?php

/**
 * Description of ValidationError
 * @author goddy
 *
 * Created on: May 23, 2026 at 12:10:13 PM
 */

namespace features\validation {

    /**
     * Represents a validation error for a specific field.
     *
     * Encapsulates details about a failed validation, including the field name,
     * expected and found values, error code, and a human-readable description.
     * Implements JsonSerializable for structured error output in JSON responses.
     *
     * @author goddy
     * @created May 23, 2026 at 12:10:13 PM
     */
    final class ValidationError implements \JsonSerializable
    {

        /**
         * @var string The name of the field that failed validation.
         */
        private readonly string $fieldName;

        /**
         * @var string|null Optional error code to categorize the validation failure.
         */
        private readonly ?string $errorCode;

        /**
         * @var mixed The expected value or condition for the field.
         */
        private readonly mixed $expectedValue;

        /**
         * @var mixed The actual value found during validation.
         */
        private readonly mixed $foundValue;

        /**
         * @var string Human-readable description of the validation error.
         */
        private readonly string $errorDescription;

        /**
         * Constructs a new ValidationError instance.
         *
         * @param string      $fieldName       The name of the field that failed validation.
         * @param string|null $errorCode       Optional error code for categorization.
         * @param mixed      $expectedValue   The expected value or condition.
         * @param mixed      $foundValue      The actual value encountered.
         * @param string      $errorDescription A descriptive message explaining the error.
         */
        public function __construct(
                string $fieldName,
                ?string $errorCode,
                mixed $expectedValue,
                mixed $foundValue,
                string $errorDescription
        )
        {
            $this->fieldName = $fieldName;
            $this->errorCode = $errorCode;
            $this->expectedValue = $expectedValue;
            $this->foundValue = $foundValue;
            $this->errorDescription = $errorDescription;
        }

        /**
         * Serializes the validation error into an associative array.
         *
         * Provides a structured representation suitable for JSON encoding,
         * including field name, error code, expected and found values,
         * and the error description.
         *
         * @return array<string, mixed> The serialized validation error data.
         */
        public function jsonSerialize(): array
        {
            return [
                'field_name' => $this->fieldName,
                'error_code' => $this->errorCode,
                'expected_value' => $this->expectedValue,
                'found_value' => $this->foundValue,
                'error_description' => $this->errorDescription,
            ];
        }
    }

}
