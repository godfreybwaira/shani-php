<?php

/**
 * Description of HttpMessageDto
 * @author coder
 *
 * Created on: Feb 25, 2025 at 8:11:56 PM
 */

namespace shani\http {

    use library\http\HttpStatus;
    use shani\contracts\DataDto;

    final class HttpMessageDto implements DataDto
    {

        private readonly HttpStatus $status;
        private readonly ?string $message;

        public function __construct(HttpStatus $status, ?string $message = null)
        {
            $this->status = $status;
            $this->message = $message;
        }

        #[\Override]
        public function asMap(): array
        {
            return[
                'code' => $this->status->value,
                'content' => $this->message ?? $this->status->getMessage()
            ];
        }
    }

}
