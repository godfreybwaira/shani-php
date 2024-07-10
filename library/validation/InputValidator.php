<?php

/**
 * Description of Data
 * @author coder
 *
 * Created on: Mar 4, 2024 at 9:56:18 AM
 */

namespace library\validation {

    final class InputValidator
    {

        private array $inputs;
        private ?array $names, $callbacks, $messages, $labels;

        /**
         * Create input validator
         * @param array $userInputs Key-value pair of user input
         */
        public function __construct(array &$userInputs)
        {
            $this->inputs = $userInputs;
            $this->names = $this->callbacks = $this->messages = $this->labels = [];
        }

        /**
         * Create a validation constraint.
         * @param string $inputName Named input that must match the user input name.
         * @param callable $callback A callback function to execute on a given input value.
         * A callback must accept a string value as input value i.e $callback($value):?string
         * @param string|null $errorMessage Error message to return in case of any validation error
         * @param string|null $inputLabel User friendly input label that represent input name
         * @return self
         */
        public function setConstraint(string $inputName, callable $callback, ?string $errorMessage = null, ?string $inputLabel = null): self
        {
            $this->names[] = $inputName;
            $this->labels[] = $inputLabel;
            $this->callbacks[] = $callback;
            $this->messages[] = $errorMessage;
            return $this;
        }

        /**
         * Validate user inputs using given constraints.
         * @return array|null Validation error message or null if no validation error
         */
        public function validate(): ?array
        {
            $results = null;
            foreach ($this->names as $key => $name) {
                if (!isset($this->inputs[$name])) {
                    $results[$name][] = 'column not available.';
                }
                $cb = $this->callbacks[$key];
                $msg = $cb($this->inputs[$name]);
                if (!empty($msg)) {
                    $results[$name][] = $this->messages[$key] ?? ($this->labels[$key] ?? $name) . ' ' . $msg;
                }
            }
            return $results;
        }
    }

}
