<?php

/**
 * Description of Components
 * @author coder
 *
 * Created on: Jun 12, 2025 at 1:57:16 PM
 */

namespace apps\demo\modules\shani\logic\controllers\get {

    use features\documentation\Generator as Documentation;
    use features\pwa\PwaBuilder;
    use features\utils\HttpClient;
    use features\utils\MediaType;
    use features\utils\URI;
    use gui\WebUIBuilder;
    use shani\http\HttpHeader;
    use shani\http\HttpResponse;
    use shani\http\ResponseEntity;
    use shani\launcher\App;

    final class Components
    {

        private readonly App $app;

        public function __construct(App $app)
        {
            $this->app = $app;
        }

        public function index(): HttpResponse
        {
            $builder = new WebUIBuilder();
            $builder->description('Shani web framework')
                    ->title('Home Page II')
                    ->setPwaBuilder(new PwaBuilder($this->app->storage->uri('/pwa/0/manifest.json'), $this->app->storage->uri('/pwa/0/sw.js')))
                    ->view('/body');
            return HttpResponse::withBody($builder);
        }

        public function all(): HttpResponse
        {
            return HttpResponse::withBody(new WebUIBuilder());
        }

        public function stream(): \Closure
        {
            $this->app->response->header()->addOne(HttpHeader::CONTENT_TYPE, MediaType::JSON);
            return function () {
                $db = $this->app->config->getDatabase();
                $rows = $db->find('users');
                while (true) {
                    sleep(1);
                    if ($rows->valid()) {
                        yield $rows->current();
                        $rows->next();
                    } else {
                        yield; //terminate streaming
                    }
                }
            };
        }

        public function users(): \Closure
        {
            $db = $this->app->config->getDatabase();
            $this->app->response->header()->addOne(HttpHeader::CONTENT_TYPE, MediaType::JSON);
            $rows = $db->find('users');
            return function () use (&$rows) {
                foreach ($rows as $row) {
                    yield $row;
                }
            };
        }

        public function inputs(): HttpResponse
        {
            return HttpResponse::withBody(new WebUIBuilder());
        }

        public function containers(): HttpResponse
        {
            return HttpResponse::withBody(new WebUIBuilder());
        }

        public function modals(): HttpResponse
        {
            $builder = new WebUIBuilder();
            $builder->attr->addIfAbsent('type', $this->app->request->query->getOne('type'));
            return $builder;
        }

        public function toaster(): HttpResponse
        {
            return HttpResponse::withBody(new WebUIBuilder());
        }

        public function timeline(): HttpResponse
        {
            return HttpResponse::withBody(new WebUIBuilder());
        }

        public function shani(): HttpResponse
        {
            return HttpResponse::withBody(new WebUIBuilder());
        }

        public function redirect(): HttpResponse
        {
            return HttpResponse::withBody(new WebUIBuilder());
        }

        public function generator(): Documentation
        {
            $this->app->response->header()->addOne(HttpHeader::CONTENT_TYPE, MediaType::JSON);
            return new Documentation($this->app);
        }

        public function card(): HttpResponse
        {
            return HttpResponse::withBody(new WebUIBuilder());
        }

        public function bindings(): HttpResponse
        {
            return HttpResponse::withBody(new WebUIBuilder());
        }

        public function nodes(): HttpResponse
        {
            return HttpResponse::withBody(new WebUIBuilder());
        }

        public function client(): HttpResponse
        {
            $client = new HttpClient(new URI('https://dev.shani.v2.local'));
            $client->enableAsync(false)->enableSSLVerification(false);
            $cb = null;
            $client->get('/shani/0/components/0/stream', function (ResponseEntity $res) use (&$cb) {
                $cb = function ()use (&$res) {
                    yield $res->body();
                };
            });
            return HttpResponse::withBody($cb);
        }
    }

}
