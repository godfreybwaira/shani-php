<?php

namespace apps\abc\modules\users\logic\controllers\get {

    use shani\http\HttpResponse;
    use gui\WebUIBuilder;
    use shani\launcher\App;

    final class Account
    {

        private readonly App $app;

        public function __construct(App $app)
        {
            $this->app = $app;
        }

        public function index(): HttpResponse
        {
            return HttpResponse::withBody(new WebUIBuilder());
        }
    }

}

