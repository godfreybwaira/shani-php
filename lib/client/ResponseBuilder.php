<?php

/**
 * Description of Response
 * @author coder
 *
 * Created on: Apr 4, 2024 at 9:28:21 PM
 */

namespace lib\client {

    use lib\http\HttpCookie;
    use lib\http\HttpHeader;
    use lib\http\HttpStatus;
    use lib\http\RequestEntity;
    use lib\http\ResponseEntity;
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
                $header = explode(': ', $line);
                $headers[$header[0]] = $header[1] ?? null;
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
            self::setReflectionHeaders($request, $headers);
            $response = new ResponseEntity($request, $status, $headers);
            $response->setBody(self::getContent($stream, $headerSize));
            return $response;
        }

        private static function setReflectionHeaders(RequestEntity &$request, HttpHeader &$headers)
        {
            $cookies = new HttpCookie($headers->getOne(HttpHeader::SET_COOKIE));
            foreach ($cookies as $cookie) {
                $request->header()->setCookie($cookie);
            }
            $etag = $headers->getOne(HttpHeader::ETAG);
            if ($etag !== null) {
                $request->header()->addOne(HttpHeader::IF_NONE_MATCH, $etag);
            }
            $lastModified = $headers->getOne(HttpHeader::LAST_MODIFIED);
            if ($lastModified !== null) {
                $request->header()->addOne(HttpHeader::IF_MODIFIED_SINCE, $lastModified);
            }
        }
    }

}
