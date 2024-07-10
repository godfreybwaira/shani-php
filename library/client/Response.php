<?php

/**
 * Description of Response
 * @author coder
 *
 * Created on: Apr 4, 2024 at 9:28:21 PM
 */

namespace library\client {

    final class Response
    {

        private \CurlHandle $curl;
        private array $headers = [];
        private int $code, $headerSize, $bodySize;
        private ?string $body = null, $raw = null;
        private $stream;

        public function __construct(\CurlHandle &$curl, &$stream)
        {
            $this->headerSize = curl_getinfo($curl, CURLINFO_HEADER_SIZE);
            $this->bodySize = curl_getinfo($curl, CURLINFO_SIZE_DOWNLOAD);
            $this->code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $this->stream = $stream;
            $this->curl = $curl;
        }

        /**
         * Get error returned by remote server
         * @return string|null error or null if no error returned
         */
        public function error(): ?string
        {
            $error = curl_error($this->curl);
            return !empty($error) ? $error : null;
        }

        public function headers($names = null, bool $selected = true)
        {
            if (empty($this->headers)) {
                fseek($this->stream, 0);
                $raw = fread($this->stream, $this->headerSize - 1);
                $lines = explode("\r\n", trim($raw));
                foreach ($lines as $line) {
                    if (strpos($line, ':') === false) {
                        continue;
                    }
                    list($key, $value) = explode(': ', $line, 2);
                    $this->headers[strtolower($key)] = $value;
                }
            }
            return \library\Map::get($this->headers, $names, $selected);
        }

        public function raw(): string
        {
            return $this->raw ??= self::read($this->stream, 0);
        }

        public function asArray(): ?array
        {
            $type = \library\Mime::explode($this->headers('content-type'));
            if (!empty($type[1])) {
                return \library\DataConvertor::convertFrom($this->body(), $type[1]);
            }
            return null;
        }

        public function bodySize(): int
        {
            return $this->bodySize;
        }

        public function size(): int
        {
            return $this->bodySize + $this->headerSize;
        }

        public function body(): string
        {
            return $this->body ??= self::read($this->stream, $this->headerSize);
        }

        private static function read(&$stream, int $offset = 0): string
        {
            $data = null;
            fseek($stream, $offset);
            while (!feof($stream)) {
                $data .= fread($stream, \library\Utils::BUFFER_SIZE);
            }
            return $data;
        }

        public function stream()
        {
            return $this->stream;
        }

        public function statusCode(): int
        {
            return $this->code;
        }
    }

}
