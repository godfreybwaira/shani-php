<?php

/**
 * Description of ErrorResponse
 * @author goddy
 *
 * @since May 28, 2026 at 8:09:44 PM
 */

namespace features\exceptions {

    use features\exceptions\client\MethodArgumentNotValidException;

    final class ErrorResponse
    {

        public static function create(int $errorCode, \Throwable $ex): array
        {
            $message = $ex instanceof MethodArgumentNotValidException ? json_decode($ex->getMessage(), true) : $ex->getMessage();
            return [
                'error_code' => $errorCode,
                'error_description' => $message,
                'timestamp' => SHANI_CURRENT_TIMESTAMP
            ];
        }
    }

}
