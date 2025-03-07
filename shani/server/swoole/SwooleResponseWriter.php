<?php

/**
 * Description of SwooleResponseWriter
 * @author coder
 *
 * Created on: Mar 25, 2024 at 1:26:42 PM
 */

namespace shani\server\swoole {

    use library\http\HttpStatus;
    use library\http\ResponseEntity;
    use shani\contracts\ResponseWriter;
    use Swoole\Http\Response as SwooleResponse;

    final class SwooleResponseWriter implements ResponseWriter
    {

        private readonly SwooleResponse $res;

        public function __construct(SwooleResponse &$res)
        {
            $this->res = $res;
        }

        private function sendHeaders(ResponseEntity &$res): self
        {
            if ($this->res->isWritable()) {
                $status = $res->status();
                $this->res->status($status->value, $res->statusMessage() ?? $status->getMessage());
                $headers = $res->header()->getAll();
                foreach ($headers as $key => $value) {
                    $this->res->header($key, $value);
                }
            }
            return $this;
        }

        public function send(ResponseEntity &$res, bool $sendOnlyHeaders = false): self
        {
            $this->sendHeaders($res);
            if (!$sendOnlyHeaders) {
                $this->res->write($res->body());
            }
            return $this;
        }

        public function close(ResponseEntity &$res, bool $sendOnlyHeaders = false): self
        {
            $this->sendHeaders($res);
            if (!$sendOnlyHeaders) {
                $this->res->end($res->body());
            }
            return $this;
        }

        public function redirect(string $url, HttpStatus &$status): self
        {
            $this->res->redirect($url, $status->value);
            return $this;
        }

        public function sendFile(ResponseEntity &$res, string $absoluteFilepath, int $startByte, int $chunkSize): self
        {
            $this->sendHeaders($res);
            $this->res->sendfile($absoluteFilepath, $startByte, $chunkSize);
            return $this;
        }
    }

}
