<?php

namespace apps\blog\v1\modules\users\logic\controllers\get {

    use apps\blog\v1\modules\users\logic\services\AcademicsService;
    use shani\http\HttpResponse;
    use gui\WebUIBuilder;
    use shani\launcher\App;

    final class Academics
    {

        private readonly App $app;
        private readonly AcademicsService $service;

        public function __construct(App $app)
        {
            $this->app = $app;
            $this->service = new AcademicsService();
        }

        public function index(): HttpResponse
        {
            return HttpResponse::withBody(new WebUIBuilder());
        }
    }

}

