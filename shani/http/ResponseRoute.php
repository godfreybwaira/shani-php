<?php

/**
 * Description of ResponseRoute
 * @author goddy
 *
 * Created on: Oct 18, 2025 at 12:53:44 PM
 */

namespace shani\http {

    use gui\WebUI2;
    use lib\DataConvertor;
    use lib\http\HttpHeader;
    use lib\http\output\FileOutput;
    use lib\http\output\MixedOutput;
    use lib\MediaType;
    use shani\exceptions\CustomException;

    final class ResponseRoute
    {

        private readonly App $app;
        private string $subtype;

        public function __construct(App $app, $output)
        {
            $this->app = $app;
            $this->subtype = $app->response->subtype();
            if ($output instanceof \JsonSerializable) {
                $this->handleSerializableOutput($output);
            } elseif ($output instanceof MixedOutput) {
                $this->handleMixedOutput($output);
            } elseif ($output instanceof FileOutput) {
                $this->handleFileOutput($output);
            } else if ($output !== null) {
                throw CustomException::serverError($this, 'Could not understand the response body. Expected JsonSerializable, MixedOutput or FileOutput');
            }
        }

        private function handleSerializableOutput(?\JsonSerializable $data): void
        {
            $content = $data?->jsonSerialize();
            if ($this->subtype === DataConvertor::TYPE_JS) {
                $this->sendJsonp($content, $this->subtype);
            } else {
                $this->app->response->setBody(DataConvertor::convertTo($content, $this->subtype), $this->subtype);
                $this->app->send();
            }
        }

        private function handleMixedOutput(MixedOutput $output): void
        {
            if ($this->subtype === DataConvertor::TYPE_HTML) {
                $content = WebUI2::render($this->app, $output);
                $this->sendHtml($content, $this->subtype);
            } else if ($this->subtype === DataConvertor::TYPE_SSE) {
                $content = WebUI2::render($this->app, $output);
                $this->sendSse($content, $this->subtype);
            } else {
                $this->handleSerializableOutput($output->builder->data());
            }
        }

        private function handleFileOutput(FileOutput $output): void
        {

        }

        private function sendHtml(string $content): void
        {
            $this->app->response->setBody($content, $this->subtype)->header()
                    ->addIfAbsent(HttpHeader::CONTENT_TYPE, MediaType::TEXT_HTML);
        }

        private function sendJsonp(?array $content): void
        {
            $callback = $this->app->request->query->getOne('callback', 'callback');
            $data = $callback . '(' . json_encode($content) . ');';
            $this->app->response->setBody($data, $this->subtype)->header()
                    ->addIfAbsent(HttpHeader::CONTENT_TYPE, MediaType::JS);
        }

        private function sendSse(string $content): void
        {
            $this->app->response->setBody(DataConvertor::toEventStream($content), $this->subtype)
                    ->header()->addIfAbsent(HttpHeader::CACHE_CONTROL, 'no-cache')
                    ->addIfAbsent(HttpHeader::CONTENT_TYPE, MediaType::EVENT_STREAM);
        }
    }

}
