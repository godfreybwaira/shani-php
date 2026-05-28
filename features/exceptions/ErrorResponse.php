<?php

/**
 * Description of ErrorResponse
 * @author goddy
 *
 * Created on: May 28, 2026 at 8:09:44 PM
 */

namespace features\exceptions {

    final class ErrorResponse implements \JsonSerializable
    {

        private readonly int $errorCode;
        private readonly string $errorDescription;

        private function __construct(int $errorCode, string $errorDescription)
        {
            $this->errorCode = $errorCode;
            $this->errorDescription = $errorDescription;
        }

        #[\Override]
        public function jsonSerialize(): array
        {
            return [
                'error_code' => $this->errorCode,
                'error_description' => $this->errorDescription,
                'timestamp' => SHANI_CURRENT_TIMESTAMP
            ];
        }

        public static function create(int $errorCode, string $errorDescription): self
        {
            return new self($errorCode, $errorDescription);
        }
    }

}
