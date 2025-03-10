<?php

/**
 * Description of ResponseEntity
 * @author coder
 *
 * Created on: Feb 26, 2025 at 5:10:06 PM
 */

namespace library\http {

    use library\DataConvertor;
    use library\DataCompressionLevel;
    use library\MediaType;
    use shani\contracts\HttpCookie;

    final class ResponseEntity extends HttpEntity
    {

        /**
         * HTTP request associated with this response
         * @var RequestEntity
         */
        public readonly RequestEntity $request;
        private DataCompressionLevel $compression;
        private int $compressionMinSize = 1024; //1KB
        private ?string $statusMessage = null, $body = null;
        private HttpStatus $status;

        public function __construct(RequestEntity $request, HttpStatus $status, HttpHeader $headers)
        {
            parent::__construct($headers, $request->protocol);
            $this->status = $status;
            $this->request = $request;
            $this->compression = DataCompressionLevel::DISABLE;
        }

        public function type(): string
        {
            $contentType = $this->headers->get(HttpHeader::CONTENT_TYPE);
            if (!empty($contentType)) {
                return MediaType::explode($contentType)[1];
            }
            $parts = explode('.', $this->request->uri->path);
            $size = count($parts);
            if ($size > 1) {
                return strtolower($parts[$size - 1]);
            }
            return MediaType::explode($this->request->header()->get(HttpHeader::ACCEPT))[1] ?? '';
        }

        /**
         * Get HTTP body size
         * @return int
         */
        public function bodySize(): int
        {
            return $this->body === null ? 0 : mb_strlen($this->body);
        }

        /**
         * Get HTTP response size (including header size)
         * @return int
         */
        public function size(): int
        {
            return $this->bodySize() + $this->headers->size();
        }

        /**
         * Get HTTP status
         * @return HttpStatus
         */
        public function status(): HttpStatus
        {
            return $this->status;
        }

        /**
         * Get HTTP status message
         * @return string|null
         */
        public function statusMessage(): ?string
        {
            return $this->statusMessage;
        }

        private function compress(string &$content): self
        {
            if ($this->compression === DataCompressionLevel::DISABLE || $this->compressionMinSize >= $this->bodySize()) {
                $this->body = $content;
                $this->headers->set(HttpHeader::CONTENT_LENGTH, $this->bodySize());
                return $this;
            }
            $encoding = $this->request->header()->get(HttpHeader::ACCEPT_ENCODING);
            if (str_contains($encoding, 'gzip')) {
                $this->headers->set(HttpHeader::CONTENT_ENCODING, 'gzip');
                $this->body = gzencode($content, $this->compression->value);
            } elseif (str_contains($encoding, 'deflate')) {
                $this->headers->set(HttpHeader::CONTENT_ENCODING, 'deflate');
                $this->body = gzdeflate($content, $this->compression->value);
            } elseif (str_contains($encoding, 'compress')) {
                $this->headers->set(HttpHeader::CONTENT_ENCODING, 'compress');
                $this->body = gzcompress($content, $this->compression->value);
            } else {
                $this->body = $content;
            }
            $this->headers->set(HttpHeader::CONTENT_LENGTH, $this->bodySize());
            return $this;
        }

        /**
         * Get response body
         * @return string|null
         */
        public function body(): ?string
        {
            return $this->body;
        }

        public function setBody(string $content, ?string $type = null): self
        {
            if (!$this->headers->has(HttpHeader::CONTENT_TYPE)) {
                $this->headers->set(HttpHeader::CONTENT_TYPE, match ($type ?? $this->type()) {
                    DataConvertor::TYPE_JSON => MediaType::JSON,
                    DataConvertor::TYPE_XML => MediaType::XML,
                    DataConvertor::TYPE_CSV => MediaType::TEXT_CSV,
                    DataConvertor::TYPE_YAML => MediaType::TEXT_YAML,
                    DataConvertor::TYPE_HTML => MediaType::TEXT_HTML,
                    DataConvertor::TYPE_SSE => MediaType::EVENT_STREAM,
                    default => MediaType::BIN
                });
            }
            return $this->compress($content);
        }

        public function setStatus(HttpStatus $status, ?string $message = null): self
        {
            $this->status = $status;
            $this->statusMessage = $message;
            return $this;
        }

        /**
         * Set HTTP cache commands
         * @param HttpCache $cache Cache object
         * @return self
         */
        public function setCache(HttpCache $cache): self
        {
            $this->headers->set(HttpHeader::ETAG, $cache->etag())
                    ->set(HttpHeader::CACHE_CONTROL, $cache);
            return $this;
        }

        /**
         * Clear HTTP cache commands
         * @return self
         */
        public function clearCache(): self
        {
            $this->headers->remove(HttpHeader::ETAG)
                    ->remove(HttpHeader::CACHE_CONTROL);
            return $this;
        }

        public function clearCookies(): self
        {
            $this->headers->remove(HttpHeader::SET_COOKIE);
            return $this;
        }

        public function setCookie(HttpCookie $cookie): self
        {
            $this->headers->set(HttpHeader::SET_COOKIE, [$cookie->name() => $cookie]);
            return $this;
        }

        /**
         * The Link header is used to provide relationships between the current
         * document and other resources.
         * @param array $links Associative array where key is the link name and value is the actual link
         * @return self
         */
        public function setLink(array $links): self
        {
            $lnk = null;
            foreach ($links as $name => $link) {
                $lnk .= ',<' . $link . '>; rel="' . $name . '"';
            }
            $this->headers->set(HttpHeader::LINK, substr($lnk, 1));
            return $this;
        }

        /**
         * Output a file to user agent
         * @param string $filename filename to send
         * @return self
         */
        public function saveAs(string $filename = null): self
        {
            $name = $filename ? '; filename="' . $filename . '"' : '';
            $this->headers->set(HttpHeader::CONTENT_DISPOSITION, 'attachment' . $name);
            return $this;
        }

        /**
         * Set data compression strategy for a response body using user Accept-Encoding header.
         * This function cannot be called after <code>setBody</code> function.
         * @param DataCompressionLevel $level Compression level
         * @param int $minSize Minimum number of bytes to compress. Default size is 1KB
         * @return self
         */
        public function setCompression(DataCompressionLevel $level, int $minSize = 1024): self
        {
            $this->compression = $level;
            $this->compressionMinSize = $minSize;
            return $this;
        }
    }

}
