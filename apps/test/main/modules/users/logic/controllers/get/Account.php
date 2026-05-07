<?php

namespace apps\test\main\modules\users\logic\controllers\get {

    use apps\test\main\modules\users\logic\services\AccountService;
    use shani\http\HttpResponse;
    use gui\WebUIBuilder;
    use shani\launcher\App;

    final class Account
    {

        private readonly App $app;
        private readonly AccountService $service;

        public function __construct(App $app)
        {
            $this->app = $app;
            $this->service = new AccountService();
        }

        public function index(): HttpResponse
        {
            return HttpResponse::withBody(new WebUIBuilder());
        }
    }

}

