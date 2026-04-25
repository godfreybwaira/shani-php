<?php

/**
 * Description of Sw
 * @author coder
 *
 * Created on: Jun 12, 2025 at 1:57:16 PM
 */

namespace apps\demo\modules\pwa\logic\controllers\get {

    use features\storage\LocalStorage;
    use shani\http\FileOutputStream;
    use shani\http\HttpHeader;
    use shani\http\HttpResponse;
    use shani\launcher\App;

    final class Sw
    {

        private readonly App $app;

        public function __construct(App $app)
        {
            $this->app = $app;
        }

        public function index(): HttpResponse
        {
            $file = LocalStorage::assetPath('/js/pwa-sw.js');
            $this->app->response->header()->addOne(HttpHeader::SERVICE_WORKER_ALLOWED, '/');
            return new HttpResponse(new FileOutputStream($file));
        }
    }

}
