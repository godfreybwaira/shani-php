<?php

/**
 * Description of CgiHttpResponseWriter
 * @author coder
 *
 * Created on: May 22, 2025 at 9:45:50 AM
 */

namespace shani\servers\cgi {

    use shani\http\ResponseEntity;
    use shani\contracts\ResponseWriterInterface;

    final class CgiHttpResponseWriter implements ResponseWriterInterface
    {

        public function sendHeaders(ResponseEntity $res): self
        {
            if (!headers_sent()) {
                $status = $res->status();
                $statusLine = $res->protocol . ' ' . $status->value . ' ' . ($res->statusMessage() ?? $status->getMessage());
                header($statusLine);
                $headers = $res->header()->entrySet();
                foreach ($headers as $key => $value) {
                    if (is_array($value)) {
                        header($key . ':' . implode(',', $value));
                    } else {
                        header($key . ':' . $value);
                    }
                }
            }
            return $this;
        }

        public function send(ResponseEntity $res): self
        {
            return $this->sendHeaders($res)->sendBody($res);
        }

        public function close(ResponseEntity $res): self
        {
            return $this->send($res);
        }

        public function sendBody(ResponseEntity $res): self
        {
            echo $res->body();
            self::flush();
            return $this;
        }

        public function streamFile(ResponseEntity $res, string $filepath, int $startByte, int $chunkSize): self
        {
            $this->sendHeaders($res);
            $stream = fopen($filepath, 'rb');
            fseek($stream, $startByte);
            while (!feof($stream)) {
                echo fread($stream, $chunkSize);
                self::flush();
            }
            fclose($stream);
            return $this;
        }

        private static function flush(): void
        {
            flush();
            if (ob_get_level() > 0) {
                ob_flush();
            }
        }

        public function isClosed(): bool
        {
            return headers_sent();
        }
    }

}