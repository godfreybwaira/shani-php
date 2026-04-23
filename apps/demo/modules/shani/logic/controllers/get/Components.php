<?php

/**
 * Description of Components
 * @author coder
 *
 * Created on: Jun 12, 2025 at 1:57:16 PM
 */

namespace apps\demo\modules\shani\logic\controllers\get {

    use features\pwa\PwaBuilder;
    use gui\WebUIBuilder;
    use features\utils\HttpClient;
    use shani\http\HttpHeader;
    use shani\http\ResponseEntity;
    use features\utils\MediaType;
    use features\utils\URI;
    use features\documentation\Generator as Documentation;
    use shani\launcher\App;

    final class Components
    {

        private readonly App $app;

        public function __construct(App $app)
        {
            $this->app = $app;
        }

        public function index(): WebUIBuilder
        {
            $storage = $this->app->storage();
            $builder = new WebUIBuilder();
            $builder->description('Shani web framework')
                    ->title('Home Page II')
                    ->setPwaBuilder(new PwaBuilder($storage->uri('/pwa/0/manifest.json'), $storage->uri('/pwa/0/sw.js')))
                    ->view('/body');
            return $builder;
        }

        public function all(): WebUIBuilder
        {
            return new WebUIBuilder();
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

        public function inputs(): WebUIBuilder
        {
            return new WebUIBuilder();
        }

        public function containers(): WebUIBuilder
        {
            return new WebUIBuilder();
        }

        public function modals(): WebUIBuilder
        {
            $builder = new WebUIBuilder();
            $builder->attr->addIfAbsent('type', $this->app->request->query->getOne('type'));
            return $builder;
        }

        public function toaster(): WebUIBuilder
        {
            return new WebUIBuilder();
        }

        public function timeline(): WebUIBuilder
        {
            return new WebUIBuilder();
        }

        public function shani(): WebUIBuilder
        {
            return new WebUIBuilder();
        }

        public function redirect(): WebUIBuilder
        {
            return new WebUIBuilder();
        }

        public function generator(): Documentation
        {
            $this->app->response->header()->addOne(HttpHeader::CONTENT_TYPE, MediaType::JSON);
            return new Documentation($this->app);
        }

        public function card(): WebUIBuilder
        {
            return new WebUIBuilder();
        }

        public function bindings(): WebUIBuilder
        {
            return new WebUIBuilder();
        }

        public function nodes(): WebUIBuilder
        {
            return new WebUIBuilder();
        }

        public function client(): \Closure
        {
            $client = new HttpClient(new URI('https://dev.shani.v2.local'));
            $client->enableAsync(false)->enableSSLVerification(false);
            $cb = null;
            $client->get('/shani/0/components/0/stream', function (ResponseEntity $res) use (&$cb) {
                $cb = function ()use (&$res) {
                    yield $res->body();
                };
            });
            return $cb;
        }
    }

}
