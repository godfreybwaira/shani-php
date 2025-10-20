<?php

/**
 * Description of HttpWriter
 * @author goddy
 *
 * Created on: Oct 18, 2025 at 12:53:44 PM
 */

namespace shani\http {

    use gui\WebUIBuilder;
    use gui\WebUI;
    use lib\DataConvertor;
    use lib\http\FileOutput;
    use lib\http\HttpHeader;
    use lib\http\HttpStatus;
    use lib\MediaType;
    use shani\contracts\ResponseWriter;

    final class HttpWriter
    {

        private readonly App $app;
        private readonly ResponseWriter $writer;

        public function __construct(App $app, ResponseWriter $writer)
        {
            $this->app = $app;
            $this->writer = $writer;
        }

        /**
         * Send content to a client application
         * @param \JsonSerializable|WebUIBuilder|FileOutput|null $output Output object to send
         * @return void
         */
        public function send(\JsonSerializable|WebUIBuilder|FileOutput|null $output): void
        {
            $subtype = $this->app->response->subtype();
            if ($output instanceof \JsonSerializable) {
                $this->handleSerializableOutput($output, $subtype);
            } elseif ($output instanceof WebUIBuilder) {
                $this->handleUIBuilderOutput($output, $subtype);
            } elseif ($output instanceof FileOutput) {
                $this->handleFileOutput($output);
                return;
            }
            $this->write();
        }

        private function handleSerializableOutput(?\JsonSerializable $data, string $subtype): void
        {
            $content = $data?->jsonSerialize();
            if ($subtype === DataConvertor::TYPE_JS) {
                $this->sendJsonp($content, $subtype);
            } else {
                $this->app->response->setBody(DataConvertor::convertTo($content, $subtype), $subtype);
            }
        }

        private function handleUIBuilderOutput(WebUIBuilder $builder, string $subtype): void
        {
            if ($subtype === DataConvertor::TYPE_HTML) {
                $content = WebUI::render($this->app, $builder);
                $this->app->response->setBody($content, $subtype);
            } else if ($subtype === DataConvertor::TYPE_SSE) {
                $content = WebUI::render($this->app, $builder);
                $this->sendSse($content, $subtype);
            } else {
                $this->handleSerializableOutput($builder->getData(), $subtype);
            }
        }

        /**
         * Stream a file as HTTP response
         * @param FileOutput $output
         * @return void
         */
        private function handleFileOutput(FileOutput $output): void
        {
            if (!is_readable($output->filepath)) {
                $this->app->response->setStatus(HttpStatus::NOT_FOUND);
                $this->write();
                return;
            }
            if ($output->filename !== null) {
                $this->app->response->saveAs($output->filename);
            }
            $file = stat($output->filepath);
            $start = 0;
            $end = $file['size'] - 1;
            $range = $this->app->request->header()->getOne(HttpHeader::RANGE) ?? '=0-';
            if ($range === '=0-' && $file['size'] <= $output->chunkSize) {
                $this->app->response->setStatus(HttpStatus::OK);
            } else {
                $start = (int) substr($range, strpos($range, '=') + 1, strpos($range, '-'));
                $end = min($start + $output->chunkSize, $file['size']) - 1;
                $this->app->response->setStatus(HttpStatus::PARTIAL_CONTENT)->header()->addAll([
                    HttpHeader::CONTENT_RANGE => "bytes $start-$end/" . $file['size'],
                    HttpHeader::ACCEPT_RANGES => 'bytes'
                ]);
                $this->app->response->header()->addOne(HttpHeader::LAST_MODIFIED, gmdate(DATE_RFC7231, $file['mtime']));
            }
            $this->doStream($output->filepath, $file['size'], $start, $end);
        }

        private function doStream(string $path, int $filesize, int $start, int $end): void
        {
            $length = $end - $start + 1;
            if ($length > 0 && $length <= $filesize) {
                $this->app->response->header()->addAll([
                    HttpHeader::CONTENT_LENGTH => $length,
                    HttpHeader::CONTENT_TYPE => MediaType::fromFilename($path)
                ]);
                if ($this->app->request->method === 'head') {
                    $this->app->response->setStatus(HttpStatus::NO_CONTENT);
                    $this->writer->close($this->app->response);
                } else {
                    $this->writer->stream($this->app->response, $path, $start, $length);
                }
            } else {
                $this->app->response->setStatus(HttpStatus::BAD_REQUEST);
                $this->write();
            }
        }

        private function sendJsonp(?array $content, string $subtype): void
        {
            $callback = $this->app->request->query->getOne('callback', 'callback');
            $data = $callback . '(' . json_encode($content) . ');';
            $this->app->response->setBody($data, $subtype);
        }

        private function sendSse(string $content, string $subtype): void
        {
            $this->app->response->setBody(DataConvertor::toEventStream($content), $subtype)
                    ->header()->addIfAbsent(HttpHeader::CACHE_CONTROL, 'no-cache');
        }

        private function write(): void
        {
            $useBuffer = $this->app->connectionStatus();
            $scheme = $this->app->request->uri->scheme();
            $buffer = $useBuffer === null ? $scheme === 'ws' || $scheme === 'wss' : $useBuffer;
            $this->app->config->responseMutator();
            $this->app->response->header()->addOne(HttpHeader::CONTENT_LENGTH, $this->app->response->bodySize());
            if ($this->app->request->method === 'head') {
                $this->app->response->setStatus(HttpStatus::NO_CONTENT);
                $this->writer->sendHeaders($this->app->response);
            } else if ($buffer) {
                $this->writer->send($this->app->response);
            } else {
                $this->writer->close($this->app->response);
            }
        }
    }

}
