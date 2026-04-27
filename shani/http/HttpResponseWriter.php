<?php

/**
 * Description of HttpWriter
 * @author goddy
 *
 * Created on: Oct 18, 2025 at 12:53:44 PM
 */

namespace shani\http {

    use features\utils\DataConvertor;
    use gui\WebUI;
    use gui\WebUIBuilder;
    use shani\contracts\ResponseWriterInterface;
    use shani\http\enums\HttpConnection;
    use shani\http\enums\HttpStatus;
    use shani\http\FileOutputStream;
    use shani\http\HttpHeader;
    use shani\launcher\App;

    final class HttpResponseWriter
    {

        private readonly App $app;
        private readonly ResponseWriterInterface $writer;

        public function __construct(App $app, ResponseWriterInterface $writer)
        {
            $this->app = $app;
            $this->writer = $writer;
        }

        private function stream(\Closure $callback, string $subtype): void
        {
            $this->app->response->setStatus(HttpStatus::PARTIAL_CONTENT);
            $this->app->response->header()
                    ->addIfAbsent(HttpHeader::CACHE_CONTROL, 'no-cache')
                    ->addOne('X-Accel-Buffering', 'no'); //disable buffering on nginx
            $this->writer->sendHeaders($this->app->response);
            foreach ($callback() as $output) {
                if ($output === null) {
                    break;
                }
                $this->decisionTree($output, $subtype);
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
        private function handleFileStreaming(FileOutputStream $output): void
        {
            if ($output->downloadable) {
                $this->app->response->saveAs($output->file->name);
            }
            $startPos = 0;
            $endPos = $output->file->size - 1;
            $range = $this->app->request->header()->getOne(HttpHeader::RANGE) ?? '=0-';
            if ($range === '=0-' && $output->file->size <= $output->chunkSize) {
                $this->app->response->setStatus(HttpStatus::OK);
            } else {
                $startPos = (int) substr($range, strpos($range, '=') + 1, strpos($range, '-'));
                $endPos = min($startPos + $output->chunkSize, $output->file->size) - 1;
                $this->app->response->setStatus(HttpStatus::PARTIAL_CONTENT)->header()->addAll([
                    HttpHeader::CONTENT_RANGE => "bytes $startPos-$endPos/" . $output->file->size,
                    HttpHeader::ACCEPT_RANGES => 'bytes',
                    'X-Accel-Buffering' => 'no', //disable buffering on nginx
                ]);
                $this->app->response->header()->addOne(HttpHeader::LAST_MODIFIED, gmdate(DATE_RFC7231, $output->file->modifiedTime));
            }
            $this->streamFile($output, $output->file->size, $startPos, $endPos);
        }

        private function streamFile(FileOutputStream $output, int $filesize, int $startPos, int $endPos): void
        {
            $length = $endPos - $startPos + 1;
            if ($length > 0 && $length <= $filesize) {
                $this->app->response->header()->addAll([
                    HttpHeader::CONTENT_LENGTH => $length,
                    HttpHeader::CONTENT_TYPE => $output->file->type
                ]);
                if ($this->app->request->method === 'head') {
                    $this->app->response->setStatus(HttpStatus::NO_CONTENT);
                    $this->writer->close($this->app->response);
                } else {
                    $this->writer->streamFile($this->app->response, $output->file->path, $startPos, $length);
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

        private function write(?HttpConnection $connection): void
        {
            $this->app->response->header()->addOne(HttpHeader::CONTENT_LENGTH, $this->app->response->bodySize());
            if ($this->app->request->method === 'head') {
                $this->app->response->setStatus(HttpStatus::NO_CONTENT);
                $this->writer->sendHeaders($this->app->response);
            } else if ($this->useBuffer($connection)) {
                $this->writer->send($this->app->response);
            } else {
                $this->writer->close($this->app->response);
            }
        }

        private function useBuffer(?HttpConnection $connection): bool
        {
            if ($connection === null || $connection === HttpConnection::AUTO) {
                return true;
            }
            $scheme = $this->app->request->uri->scheme();
            return $scheme === 'ws' || $scheme === 'wss' || $connection === HttpConnection::KEEP;
        }

        private function decisionTree(mixed $content, string $subtype): bool
        {
            if ($content instanceof WebUIBuilder) {
                $this->handleUIBuilderOutput($content, $subtype);
            } elseif ($content instanceof \JsonSerializable) {
                $this->handleSerializableOutput($content, $subtype);
            } elseif (is_string($content)) {
                $this->app->response->setBody($content, $subtype);
            } elseif ($content instanceof FileOutputStream) {
                $this->handleFileStreaming($content);
                return false;
            } elseif ($content instanceof \Closure) {
                $this->stream($content, $subtype);
                return false;
            }
            return true;
        }

        public function handleResponse(?HttpResponse $response): void
        {
            $subtype = $this->app->response->subtype();
            if ($this->decisionTree($response?->body, $subtype)) {
                $this->write($response?->connection);
            }
        }
    }

}
