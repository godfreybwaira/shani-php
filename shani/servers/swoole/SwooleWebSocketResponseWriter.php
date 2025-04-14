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
    use Swoole\WebSocket\Server;

    final class SwooleWebSocketResponseWriter implements ResponseWriter
    {

        private readonly Server $writer;
        private readonly int $connectionId;

        public function __construct(Server &$writer, int $connId)
        {
            $this->writer = $writer;
            $this->connectionId = $connId;
        }

        public function sendHeaders(ResponseEntity &$res): self
        {
            $status = $res->status();
            $firstLine = $res->protocol . ' ' . $status->value . ' ' . ($res->statusMessage() ?? $status->getMessage());
            $headers = http_build_query($res->header()->toArray());
            $this->writer->push($this->connectionId, $firstLine);
            $this->writer->push($this->connectionId, $headers);
            return $this;
        }

        public function send(ResponseEntity &$res): self
        {
            $this->sendHeaders($res);
            $this->writer->push($this->connectionId, $res->body());
            return $this;
        }

        public function close(ResponseEntity &$res): self
        {
            $this->send($res)->writer->close($this->connectionId);
            return $this;
        }

        public function stream(ResponseEntity &$res, string $filepath, int $startByte, int $chunkSize): self
        {
            $this->sendHeaders($res);
            $stream = fopen($filepath, 'rb');
            fseek($stream, $startByte);
            while (!feof($stream)) {
                $this->writer->push($this->connectionId, fread($stream, $chunkSize), WEBSOCKET_OPCODE_BINARY); // Use opcode for binary frame
            }
            fclose($stream);
            return $this;
        }
    }

}
