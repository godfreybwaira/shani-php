<?php

/**
 * Description of Response
 * @author coder
 *
 * Created on: Mar 25, 2024 at 1:31:32 PM
 */

namespace shani\adaptor {

    interface Response
    {

        public function ended(): bool;

        public function write(?string $content = null): self;

        public function sendHeaders(array $values): self;

        public function redirect(string $url, int $code): self;

        public function setStatus(int $code, string $message = ''): self;

        public function sendFile(string $path, int $start, int $chunk): self;
    }

}
