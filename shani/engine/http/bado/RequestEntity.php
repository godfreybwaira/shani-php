<?php

/**
 * Description of RequestEntity
 * @author coder
 *
 * Created on: Feb 28, 2025 at 11:44:48 PM
 */

namespace shani\engine\http\bado {

    use library\URI;
    use library\HttpHeader;
    use shani\engine\http\UploadedFile;

    final class RequestEntity extends HttpEntity
    {

        private \DateTime $time;
        private readonly URI $uri;
        private string $type, $method, $ip;
        private readonly array $files, $cookies;

        public function __construct(
                URI $uri, HttpHeader $headers, ?string $body, array $cookies, array $files,
                string $type, string $method, string $ip, \DateTime $time
        )
        {
            parent::__construct($headers, $this->decompress($body));
            $this->cookies = $cookies;
            $this->method = $method;
            $this->files = $files;
            $this->type = $type;
            $this->time = $time;
            $this->uri = $uri;
            $this->ip = $ip;
        }

        #[\Override]
        public function httpVersion(): string
        {

        }

        #[\Override]
        public function mediaType(): string
        {

        }

        #[\Override]
        public function protocol(): string
        {

        }

        #[\Override]
        public function protocolLine(): string
        {

        }

        #[\Override]
        public function protocolVersion(): float
        {

        }

        #[\Override]
        public function type(): string
        {
            return $this->type;
        }

        public function method(): string
        {
            return $this->method;
        }

        public function uri(): URI
        {
            return $this->uri;
        }

        public function withUri(URI $uri): self
        {
            $copy = clone $this;
            $copy->uri = $uri;
            return $copy;
        }

        public function ip(): string
        {
            return $this->ip;
        }

        public function time(): \DateTime
        {
            return $this->time;
        }

        public function files(): array
        {
            return $this->files;
        }

        public function cookie(string $name): ?string
        {
            return $this->cookies[$name] ?? null;
        }

        public function withFile(UploadedFile $file): self
        {
            $copy = clone $this;
            $copy->files[$file->name] = $file;
            return $copy;
        }

        /**
         * Decompress a request body using user Content-Encoding header.
         * @return self
         */
        private function decompress(?string &$data): ?string
        {
            if ($data === null || $data === '') {
                return $data;
            }
            $encoding = $this->headers->get(HttpHeader::CONTENT_ENCODING);
            if (str_contains($encoding, 'gzip')) {
                return gzdecode($data);
            }if (str_contains($encoding, 'deflate')) {
                return gzinflate($data);
            }if (str_contains($encoding, 'compress')) {
                return gzuncompress($data);
            }
            return $data;
        }
    }

}
