<?php

/**
 * Description of ComponentsController
 * @author coder
 *
 * @since Jun 12, 2025 at 1:57:16 PM
 */

namespace apps\demo\v1\modules\components\logic\controllers\get {

    use features\attributes\security\AuthenticationCheck;
    use features\attributes\security\PermissionCheck;
    use features\documentation\Generator as Documentation;
    use features\pwa\PwaBuilder;
    use features\utils\HttpClient;
    use features\utils\MediaType;
    use features\utils\URI;
    use gui\WebUIBuilder;
    use shani\http\HttpHeader;
    use shani\http\ResponseEntity;
    use shani\launcher\App;

    #[AuthenticationCheck(exempted: true)]
    #[PermissionCheck(exempted: true)]
    final class ComponentsController
    {

        private readonly App $app;

        public function __construct(App $app)
        {
            $this->app = $app;
        }

        public function index(): WebUIBuilder
        {
            $builder = new WebUIBuilder(['name' => 'shani', 'version' => '1.0']);
            $builder->description('Shani web framework')
                    ->title('Home Page II')
                    ->setPwaBuilder(new PwaBuilder($this->app->storage->uri('/pwa/0/manifest.json'), $this->app->storage->uri('/pwa/0/service-worker.js')))
                    ->view('/body');
            return $builder;
        }

        public function all(): WebUIBuilder
        {
            return new WebUIBuilder();
        }

        public function stream(): \Generator
        {
            $this->app->response->header()->addOne(HttpHeader::CONTENT_TYPE, MediaType::JSON);
            $db = $this->app->config->getDatabase();
            $rows = $db->findAll('users');
            $cb = function () use ($rows) {
                while (true) {
                    usleep(500_000);
                    if ($rows->valid()) {
                        yield $rows->current();
                        $rows->next();
                    } else {
                        yield; //terminate streaming
                    }
                }
            };
            return $cb();
        }

        public function users(): \Generator
        {
            $db = $this->app->config->getDatabase();
            $this->app->response->header()->addOne(HttpHeader::CONTENT_TYPE, MediaType::JSON);
            return $db->findAll('users');
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
            $docs = new Documentation($this->app->config->pathConfig());
            return $docs;
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

        public function client(): \Generator
        {
            $client = new HttpClient(new URI('https://dev.shani.v2.local'));
            $client->enableAsync(false)->enableSSLVerification(false);
            $cb = null;
            $client->get('/components/0/stream', function (ResponseEntity $res) use (&$cb) {
                $cb = function ()use ($res) {
                    yield $res->body();
                };
            });
            return $cb();
        }
    }

}
