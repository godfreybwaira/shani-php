<?php

/**
 * Description of CgiHttpResponseWriter
 * @author coder
 *
 * Created on: May 22, 2025 at 9:45:50 AM
 */

namespace shani\servers\cgi {

    use lib\http\ResponseEntity;
    use shani\contracts\ResponseWriter;

    final class CgiHttpResponseWriter implements ResponseWriter
    {

        public function sendHeaders(ResponseEntity &$res): self
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

        public function send(ResponseEntity &$res): self
        {
            $this->sendHeaders($res);
            echo $res->body();
            return $this;
        }

        public function close(ResponseEntity &$res): self
        {
            return $this->send($res);
        }

        public function stream(ResponseEntity &$res, string $filepath, int $startByte, int $chunkSize): self
        {
            $this->sendHeaders($res);
            $stream = fopen($filepath, 'r+b');
            fseek($stream, $startByte);
            while (!feof($stream)) {
                echo fread($stream, $chunkSize);
            }
            fclose($stream);
            return $this;
        }
    }

}