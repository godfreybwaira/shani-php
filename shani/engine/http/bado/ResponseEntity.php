<?php

/**
 * Description of ResponseEntity
 * @author coder
 *
 * Created on: Feb 26, 2025 at 5:10:06 PM
 */

namespace shani\engine\http\bado {

    use library\decode\DataCompressionLevel;
    use library\DataConvertor;
    use library\HttpHeader;
    use library\HttpStatus;
    use library\MediaType;
    use shani\contracts\ResponseDto;

    final class ResponseEntity extends HttpEntity
    {

        private HttpStatus $status;
        private readonly RequestEntity $request;
        private DataCompressionLevel $compression;
        private int $compressionMinSize = 1024; //1KB
        private ?string $statusMessage = null;

        public function __construct(RequestEntity $request, HttpStatus $status, HttpHeader $headers)
        {
            parent::__construct($headers, null);
            $this->status = $status;
            $this->request = $request;
            $this->compression = DataCompressionLevel::DISABLE;
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
            $contentType = $this->headers->get(HttpHeader::CONTENT_TYPE);
            if (!empty($contentType)) {
                return MediaType::explode($contentType)[1];
            }
            $parts = explode('.', $this->request->uri()->path());
            $size = count($parts);
            if ($size > 1) {
                return strtolower($parts[$size - 1]);
            }
            return MediaType::explode($this->request->header()->get(HttpHeader::ACCEPT))[1] ?? '';
        }

        public function status(): HttpStatus
        {
            return $this->status;
        }

        public function statusMessage(): ?string
        {
            return $this->statusMessage;
        }

        private function compress(): self
        {
            if ($this->compression === DataCompressionLevel::DISABLE || $this->compressionMinSize >= $this->bodySize()) {
                $this->headers->set(HttpHeader::CONTENT_LENGTH, $this->bodySize());
                return $this;
            }
            $encoding = $this->request->header()->get(HttpHeader::ACCEPT_ENCODING);
            if (str_contains($encoding, 'gzip')) {
                $this->headers->set(HttpHeader::CONTENT_ENCODING, 'gzip');
                $this->body = gzencode($this->body, $this->compression->value);
            } elseif (str_contains($encoding, 'deflate')) {
                $this->headers->set(HttpHeader::CONTENT_ENCODING, 'deflate');
                $this->body = gzdeflate($this->body, $this->compression->value);
            } elseif (str_contains($encoding, 'compress')) {
                $this->headers->set(HttpHeader::CONTENT_ENCODING, 'compress');
                $this->body = gzcompress($this->body, $this->compression->value);
            }
            $this->headers->set(HttpHeader::CONTENT_LENGTH, $this->bodySize());
            return $this;
        }

        public function setBody(ResponseDto $dto, ?string $type = null): self
        {
            $datatype = $type ?? $this->type();
            $this->body = DataConvertor::convertTo($dto->asMap(), $datatype);
            if (!$this->headers->has(HttpHeader::CONTENT_TYPE)) {
                $this->headers->set(HttpHeader::CONTENT_TYPE, match ($datatype) {
                    DataConvertor::TYPE_JSON => MediaType::JSON,
                    DataConvertor::TYPE_XML => MediaType::XML,
                    DataConvertor::TYPE_CSV => MediaType::TEXT_CSV,
                    DataConvertor::TYPE_YAML => MediaType::TEXT_YAML,
                    default => MediaType::BIN
                });
            }
            return $this->compress();
        }

        public function setStatus(HttpStatus $status, ?string $message = null): self
        {
            $this->status = $status;
            $this->statusMessage = $message;
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
