<?php

/**
 * Description of ServerResponse
 * @author coder
 *
 * Created on: Mar 25, 2024 at 1:31:32 PM
 */

namespace shani\contracts {

    use library\http\HttpStatus;
    use library\http\ResponseEntity;

    interface ResponseWriter
    {

        public function send(ResponseEntity &$res, bool $sendOnlyHeaders = false): self;

        public function close(ResponseEntity &$res, bool $sendOnlyHeaders = false): self;

        public function redirect(string $url, HttpStatus &$status): self;

        public function sendFile(ResponseEntity &$res, string $path, int $start, int $chunk): self;
    }

}
