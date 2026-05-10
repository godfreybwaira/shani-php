<?php

namespace apps\blog\v1\modules\dashboard\logic\controllers\get {

    use apps\blog\v1\modules\dashboard\logic\services\UsageService;
    use shani\http\HttpResponse;
    use gui\WebUIBuilder;
    use shani\launcher\App;

    final class Usage
    {

        private readonly App $app;
        private readonly UsageService $service;

        public function __construct(App $app)
        {
            $this->app = $app;
            $this->service = new UsageService();
        }

        public function index(): HttpResponse
        {
            return HttpResponse::withBody(new WebUIBuilder());
        }
    }

}

