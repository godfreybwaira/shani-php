<?php

namespace apps\blog\v1\modules\users\logic\controllers\get {

    use apps\blog\v1\modules\users\logic\services\SampleService;
    use shani\http\HttpResponse;
    use gui\WebUIBuilder;
    use shani\launcher\App;

    final class Sample
    {

        private readonly App $app;
        private readonly SampleService $service;

        public function __construct(App $app)
        {
            $this->app = $app;
            $this->service = new SampleService();
        }

        public function index(): HttpResponse
        {
            return HttpResponse::withBody(new WebUIBuilder());
        }
    }

}

