<?php

/**
 * ResponseWriter class represent a class capable of writing output to a client.
 * @author coder
 *
 * Created on: Mar 25, 2024 at 1:31:32 PM
 */

namespace shani\contracts {

    use lib\http\ResponseEntity;

    interface ResponseWriter
    {

        /**
         * Send output to a client application
         * @param ResponseEntity $res Response object
         * @return self
         */
        public function send(ResponseEntity &$res): self;

        /**
         * Send HTTP response headers to client application
         * @param ResponseEntity $res Response object
         * @return self
         */
        public function sendHeaders(ResponseEntity &$res): self;

        /**
         * Send output to a client application and close connection
         * @param ResponseEntity $res Response object
         * @return self
         */
        public function close(ResponseEntity &$res): self;

        /**
         * Send a file in a small chunks to a client application. This is useful
         * when the file being sent is too large
         * @param ResponseEntity $res Response object
         * @param string $filepath An absolute file path to be sent (streamed)
         * @param int $startByte Start byte
         * @param int $chunkSize Size of a chunk to send
         * @return self
         */
        public function stream(ResponseEntity &$res, string $filepath, int $startByte, int $chunkSize): self;
    }

}
