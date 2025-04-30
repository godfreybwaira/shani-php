<?php

/**
 * Description of ResponseEntity
 * @author coder
 *
 * Created on: Feb 26, 2025 at 5:10:06 PM
 */

namespace lib\http {

    use lib\crypto\DigitalSignature;
    use lib\crypto\Encryption;
    use lib\DataCompressionLevel;
    use lib\DataConvertor;
    use lib\MediaType;

    final class ResponseEntity extends HttpEntity
    {

        /**
         * HTTP request associated with this response
         * @var RequestEntity
         */
        private HttpStatus $status;
        public readonly RequestEntity $request;
        private DataCompressionLevel $compression;
        private int $compressionMinSize = 1024; //1KB
        private ?DigitalSignature $signature = null;
        private ?Encryption $encryption = null;
        private ?string $statusMessage = null, $rawBody = null, $signatureHeader = null;

        public function __construct(RequestEntity $request, HttpStatus $status, HttpHeader $headers)
        {
            parent::__construct($headers, $request->protocol);
            $this->status = $status;
            $this->request = $request;
            $this->compression = DataCompressionLevel::DISABLE;
        }

        /**
         * Get subtype of a response. Example for application/xml, the subtype is xml
         * @return string
         */
        public function subtype(): string
        {
            $contentType = $this->headers->getOne(HttpHeader::CONTENT_TYPE);
            if (!empty($contentType)) {
                return MediaType::subtype($contentType);
            }
            $parts = explode('.', $this->request->uri->path());
            $size = count($parts);
            if ($size > 1) {
                return strtolower($parts[$size - 1]);
            }
            return MediaType::subtype($this->request->header()->getOne(HttpHeader::ACCEPT)) ?? '';
        }

        /**
         * Get HTTP body size
         * @return int
         */
        public function bodySize(): int
        {
            return $this->rawBody === null ? 0 : mb_strlen($this->rawBody);
        }

        /**
         * Get HTTP response size (including header size)
         * @return int
         */
        public function size(): int
        {
            return $this->bodySize() + $this->headers->length();
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

        private function compress(): self
        {
            if ($this->compression === DataCompressionLevel::DISABLE || $this->compressionMinSize >= $this->bodySize()) {
                return $this;
            }
            $encoding = $this->request->header()->getOne(HttpHeader::ACCEPT_ENCODING);
            if (str_contains($encoding, 'gzip')) {
                $this->headers->addOne(HttpHeader::CONTENT_ENCODING, 'gzip');
                $this->rawBody = gzencode($this->rawBody, $this->compression->value);
            } elseif (str_contains($encoding, 'deflate')) {
                $this->headers->addOne(HttpHeader::CONTENT_ENCODING, 'deflate');
                $this->rawBody = gzdeflate($this->rawBody, $this->compression->value);
            } elseif (str_contains($encoding, 'compress')) {
                $this->headers->addOne(HttpHeader::CONTENT_ENCODING, 'compress');
                $this->rawBody = gzcompress($this->rawBody, $this->compression->value);
            }
            return $this;
        }

        /**
         * Raw response body
         * @return string|null
         */
        public function body(): ?string
        {
            return $this->rawBody;
        }

        /**
         * Set response body as string. This body will be sent to client.
         * @param string|null $content Response body content
         * @param string|null $subtype Response subtype
         * @return self
         */
        public function setBody(?string $content, ?string $subtype = null): self
        {
            if (!$this->headers->exists(HttpHeader::CONTENT_TYPE)) {
                $this->headers->addOne(HttpHeader::CONTENT_TYPE, match ($subtype ?? $this->subtype()) {
                    DataConvertor::TYPE_JSON => MediaType::JSON,
                    DataConvertor::TYPE_XML => MediaType::XML,
                    DataConvertor::TYPE_CSV => MediaType::TEXT_CSV,
                    DataConvertor::TYPE_YAML => MediaType::TEXT_YAML,
                    DataConvertor::TYPE_HTML => MediaType::TEXT_HTML,
                    DataConvertor::TYPE_SSE => MediaType::EVENT_STREAM,
                    default => MediaType::BIN
                });
            }
            if ($content === null || $content === '') {
                $this->rawBody = $content;
                return $this;
            }
            $this->rawBody = $content;
            return $this->applySignature()->compress()->applyEncryption();
        }

        public function saveAs(string $filename): self
        {
            $this->headers->setFilename($filename);
            return $this;
        }

        /**
         * Set response status code
         * @param HttpStatus $status Response status object
         * @param string|null $message Optional message that will override the default status message.
         * @return self
         */
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
            $etag = $cache->etag();
            if (!empty($etag)) {
                $this->headers->addOne(HttpHeader::ETAG, $etag);
            }
            $this->headers->addOne(HttpHeader::CACHE_CONTROL, $cache);
            return $this;
        }

        /**
         * Clear HTTP cache commands
         * @return self
         */
        public function clearCache(): self
        {
            $this->headers->delete(HttpHeader::ETAG)
                    ->delete(HttpHeader::CACHE_CONTROL);
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
            $this->headers->addOne(HttpHeader::LINK, substr($lnk, 1));
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

        private function applySignature(): self
        {
            if ($this->signature !== null) {
                $this->headers->addOne($this->signatureHeader, $this->signature->sign($this->rawBody));
            }
            return $this;
        }

        /**
         * Sign response body with provided digital signature
         * @param DigitalSignature|null $signature Digital signature object
         * @param string $headerName Header name that will hold signature
         * @return self
         */
        public function sign(?DigitalSignature $signature, string $headerName = 'X-Signature'): self
        {
            $this->signature = $signature;
            $this->signatureHeader = $headerName;
            return $this;
        }

        /**
         * Encrypt response body with the given encryption keys
         * @param Encryption|null $encription Encryption object
         * @return self
         */
        public function encrypt(?Encryption $encription): self
        {
            $this->encryption = $encription;
            return $this;
        }

        private function applyEncryption(): string
        {
            if ($this->encryption !== null) {
                $this->rawBody = $this->encryption->encrypt($this->rawBody);
            }
            return $this;
        }
    }

}
