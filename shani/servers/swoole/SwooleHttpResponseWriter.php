<?php

/**
 * Description of SwooleResponseWriter
 * @author coder
 *
 * Created on: Mar 25, 2024 at 1:26:42 PM
 */

namespace shani\servers\swoole {

    use lib\http\ResponseEntity;
    use shani\contracts\ResponseWriter;
    use Swoole\Http\Response as SwooleResponse;

    final class SwooleHttpResponseWriter implements ResponseWriter
    {

        private readonly SwooleResponse $writer;

        public function __construct(SwooleResponse &$writer)
        {
            $this->writer = $writer;
        }

        public function sendHeaders(ResponseEntity &$res): self
        {
            if ($this->writer->isWritable()) {
                $status = $res->status();
                $this->writer->status($status->value, $res->statusMessage() ?? $status->getMessage());
                $headers = $res->header()->toArray();
                foreach ($headers as $key => $value) {
                    $this->writer->header($key, $value);
                }
            }
            return $this;
        }

        public function send(ResponseEntity &$res): self
        {
            $this->sendHeaders($res)->writer->write($res->body());
            return $this;
        }

        public function close(ResponseEntity &$res): self
        {
            $this->sendHeaders($res)->writer->end($res->body());
            return $this;
        }

        public function stream(ResponseEntity &$res, string $filepath, int $startByte, int $chunkSize): self
        {
            $this->sendHeaders($res)->writer->sendfile($filepath, $startByte, $chunkSize);
            return $this;
        }
    }

}
