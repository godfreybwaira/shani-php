<?php

/**
 * Description of HttpWriter
 * @author goddy
 *
 * Created on: Oct 18, 2025 at 12:53:44 PM
 */

namespace shani\http {

    use features\utils\DataConvertor;
    use features\utils\MediaType;
    use gui\WebUI;
    use gui\WebUIBuilder;
    use shani\contracts\ResponseWriter;
    use shani\http\enums\HttpStatus;
    use shani\http\FileOutputStream;
    use shani\http\HttpHeader;
    use shani\launcher\App;

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
         * Check whether the output is already sent and the response writer is closed
         * @return bool True on success, false otherwise.
         */
        public function isClosed(): bool
        {
            return $this->writer->isClosed();
        }

        /**
         * Send content to a client application
         * @param \JsonSerializable|WebUIBuilder|FileOutputStream|null $output Output
         * object to send
         * @param bool|null $keepConnection Set whether to close the connection
         * after sending a response or to keep it open. By default, if the application
         * is running as a web socket, the connection will not be closed unless
         * you say so, otherwise the connection will be closed as soon as the
         * first response is sent.
         * @return void
         */
        public function send(\JsonSerializable|WebUIBuilder|FileOutputStream|null $output = null, ?bool $keepConnection = null): void
        {
            $subtype = $this->app->response->subtype();
            if ($output instanceof \JsonSerializable) {
                $this->handleSerializableOutput($output, $subtype);
            } elseif ($output instanceof WebUIBuilder) {
                $this->handleUIBuilderOutput($output, $subtype);
            } elseif ($output instanceof FileOutputStream) {
                $this->prepareFileStreaming($output);
                return;
            }
            $this->write($keepConnection);
        }

        /**
         * Stream data to client application. To stop streaming return null on $callback
         * @param \Closure $callback A callback to handle streaming. This callback
         * has the following signature <code>$callback():?\JsonSerializable|WebUIBuilder|string</code>
         * @return void
         */
        public function stream(\Closure $callback): void
        {
            $this->app->response->setStatus(HttpStatus::PARTIAL_CONTENT);
            $subtype = $this->app->response->subtype();
            $this->app->response->header()
                    ->addIfAbsent(HttpHeader::CACHE_CONTROL, 'no-cache')
                    ->addOne('X-Accel-Buffering', 'no'); //disable buffering on nginx
            $this->writer->sendHeaders($this->app->response);
            foreach ($callback() as $output) {
                if ($output === null) {
                    break;
                }
                if ($output instanceof \JsonSerializable) {
                    $this->handleSerializableOutput($output, $subtype);
                } elseif ($output instanceof WebUIBuilder) {
                    $this->handleUIBuilderOutput($output, $subtype);
                } else {
                    $this->app->response->setBody($output);
                }
                $this->writer->sendBody($this->app->response);
            }
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
         * @param FileOutputStream $output
         * @return void
         */
        private function prepareFileStreaming(FileOutputStream $output): void
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
            $startPos = 0;
            $endPos = $file['size'] - 1;
            $range = $this->app->request->header()->getOne(HttpHeader::RANGE) ?? '=0-';
            if ($range === '=0-' && $file['size'] <= $output->chunkSize) {
                $this->app->response->setStatus(HttpStatus::OK);
            } else {
                $startPos = (int) substr($range, strpos($range, '=') + 1, strpos($range, '-'));
                $endPos = min($startPos + $output->chunkSize, $file['size']) - 1;
                $this->app->response->setStatus(HttpStatus::PARTIAL_CONTENT)->header()->addAll([
                    HttpHeader::CONTENT_RANGE => "bytes $startPos-$endPos/" . $file['size'],
                    HttpHeader::ACCEPT_RANGES => 'bytes',
                    'X-Accel-Buffering' => 'no', //disable buffering on nginx
                ]);
                $this->app->response->header()->addOne(HttpHeader::LAST_MODIFIED, gmdate(DATE_RFC7231, $file['mtime']));
            }
            $this->streamFile($output->filepath, $file['size'], $startPos, $endPos);
        }

        private function streamFile(string $filepath, int $filesize, int $startPos, int $endPos): void
        {
            $length = $endPos - $startPos + 1;
            if ($length > 0 && $length <= $filesize) {
                $this->app->response->header()->addAll([
                    HttpHeader::CONTENT_LENGTH => $length,
                    HttpHeader::CONTENT_TYPE => MediaType::fromFilename($filepath)
                ]);
                if ($this->app->request->method === 'head') {
                    $this->app->response->setStatus(HttpStatus::NO_CONTENT);
                    $this->writer->close($this->app->response);
                } else {
                    $this->writer->streamFile($this->app->response, $filepath, $startPos, $length);
                }
            } else {
                $this->app->response->setStatus(HttpStatus::BAD_REQUEST);
                $this->write();
            }
        }

        private function sendJsonp(?array $content, string $subtype): void
        {
            $callback = $this->app->request->query->getOne('callback', 'callback');
            $data = $callback . '(' . json_encode($content, JSON_UNESCAPED_SLASHES) . ');';
            $this->app->response->setBody($data, $subtype);
        }

        private function sendSse(string $content, string $subtype): void
        {
            $this->app->response->setBody(DataConvertor::toEventStream($content), $subtype)
                    ->header()->addIfAbsent(HttpHeader::CACHE_CONTROL, 'no-cache');
        }

        private function write(?bool $keepConnection = false): void
        {
            $scheme = $this->app->request->uri->scheme();
            $buffer = $keepConnection === null ? $scheme === 'ws' || $scheme === 'wss' : $keepConnection;
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
