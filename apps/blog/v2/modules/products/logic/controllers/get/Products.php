<?php

namespace apps\blog\v2\modules\products\logic\controllers\get {

    use apps\blog\v2\modules\products\logic\services\ProductsService;
    use shani\http\HttpResponse;
    use gui\WebUIBuilder;
    use shani\launcher\App;

    final class Products
    {

        private readonly App $app;
        private readonly ProductsService $service;

        public function __construct(App $app)
        {
            $this->app = $app;
            $this->service = new ProductsService();
        }

        public function index(): HttpResponse
        {
            return HttpResponse::withBody(new WebUIBuilder());
        }

        public function categories(): HttpResponse
        {

        }
    }

}

