<?php

/**
 * Description of Components
 * @author coder
 *
 * Created on: Jun 12, 2025 at 1:57:16 PM
 */

namespace apps\demo\modules\shani\logic\controllers\get {

    use gui\pwa\PwaBuilder;
    use gui\WebUIBuilder;
    use lib\client\HttpClient;
    use lib\http\HttpHeader;
    use lib\http\ResponseEntity;
    use lib\MediaType;
    use lib\URI;
    use shani\documentation\Generator as Documentation;
    use shani\http\App;

    final class Components
    {

        private readonly App $app;

        public function __construct(App $app)
        {
            $this->app = $app;
        }

        public function index(): void
        {
            $builder = new WebUIBuilder();
            $builder->description('Shani web framework')
                    ->title('Home Page II')
                    ->setPwaBuilder(new PwaBuilder('/pwa/0/manifest.json', '/pwa/0/sw.js'))
                    ->view('/body');
            $this->app->writer->send($builder);
        }

        public function all(): void
        {
            $this->app->writer->send(new WebUIBuilder());
        }

        public function stream(): void
        {
            $this->app->response->header()->addOne(HttpHeader::CONTENT_TYPE, MediaType::JSON);
            $this->app->writer->stream(function () {
                $db = $this->app->config->database();
                $rows = $db->collect('SELECT * FROM users');
                while (true) {
                    sleep(1);
                    if ($rows->valid()) {
                        yield $rows->current();
                        $rows->next();
                    } else {
                        yield; //terminate streaming
                    }
                }
            });
        }

        public function users(): void
        {
            $db = $this->app->config->database();
            $this->app->response->header()->addOne(HttpHeader::CONTENT_TYPE, MediaType::JSON);
            $rows = $db->get('SELECT * FROM users');
            $this->app->writer->send($rows);
        }

        public function inputs(): void
        {
            $this->app->writer->send(new WebUIBuilder());
        }

        public function containers(): void
        {
            $this->app->writer->send(new WebUIBuilder());
        }

        public function modals(): void
        {
            $builder = new WebUIBuilder();
            $builder->attr->addIfAbsent('type', $this->app->request->query->getOne('type'));
            $this->app->writer->send($builder);
        }

        public function toaster(): void
        {
            $this->app->writer->send(new WebUIBuilder());
        }

        public function timeline(): void
        {
            $this->app->writer->send(new WebUIBuilder());
        }

        public function shani(): void
        {
            $this->app->writer->send(new WebUIBuilder());
        }

        public function redirect(): void
        {
            $this->app->writer->send(new WebUIBuilder());
        }

        public function generator(): void
        {
            sleep(2);
            $doc = new Documentation($this->app);
            $this->app->response->header()->addOne(HttpHeader::CONTENT_TYPE, MediaType::JSON);
            $this->app->writer->send($doc);
        }

        public function card(): void
        {
            sleep(1);
            $this->app->writer->send(new WebUIBuilder());
        }

        public function bindings(): void
        {
            $this->app->writer->send(new WebUIBuilder());
        }

        public function nodes(): void
        {
            $this->app->writer->send(new WebUIBuilder());
        }

        public function client(): void
        {
            $client = new HttpClient(new URI('https://dev.shani.v2.local'));
            $client->enableAsync(false)->enableSSLVerification(false);
            $client->get('/shani/0/components/0/stream', function (ResponseEntity $res) {
                $this->app->writer->stream(function ()use (&$res) {
                    yield $res->body();
                });
            });
            $this->app->writer->send();
        }
    }

}
