<?php

/**
 * Description of Response
 * @author coder
 *
 * Created on: Apr 4, 2024 at 9:28:21 PM
 */

namespace library\client {

    use library\http\HttpHeader;
    use library\http\HttpStatus;
    use library\http\RequestEntity;
    use library\http\ResponseEntity;
    use shani\core\Definitions;

    final class ResponseBuilder
    {

        private static function collectHeaders(&$stream, int $headerSize): HttpHeader
        {
            $headers = [];
            fseek($stream, 0);
            $raw = fread($stream, $headerSize - 1);
            $lines = explode("\r\n", trim($raw));
            foreach ($lines as $line) {
                if (!str_contains($line, ':')) {
                    continue;
                }
                list($key, $value) = explode(': ', $line, 2);
                $headers[$key] = $value;
            }
            return new HttpHeader($headers);
        }

        private static function getContent(&$stream, int $offset = 0): string
        {
            $data = null;
            fseek($stream, $offset);
            while (!feof($stream)) {
                $data .= fread($stream, Definitions::BUFFER_SIZE);
            }
            return $data;
        }

        public static function build(RequestEntity &$request, \CurlHandle &$curl, &$stream): ResponseEntity
        {
            $headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
//            $bodySize = curl_getinfo($curl, CURLINFO_SIZE_DOWNLOAD);
            $errorMessage = curl_error($curl);
            if (!empty($errorMessage)) {
                throw new \ErrorException($errorMessage);
            }
            $status = HttpStatus::from(curl_getinfo($curl, CURLINFO_HTTP_CODE));
            $headers = self::collectHeaders($stream, $headerSize);
            $response = new ResponseEntity($request, $status, $headers);
            return $response->setBody(self::getContent($stream, $headerSize));
        }
    }

}
