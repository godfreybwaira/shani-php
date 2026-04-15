<?php

/**
 * Description of Sw
 * @author coder
 *
 * Created on: Jun 12, 2025 at 1:57:16 PM
 */

namespace apps\demo\modules\pwa\logic\controllers\get {

    use gui\WebUI;
    use shani\http\FileOutputStream;
    use shani\http\HttpHeader;
    use shani\http\App;

    final class Sw
    {

        private readonly App $app;

        public function __construct(App $app)
        {
            $this->app = $app;
        }

        public function index(): FileOutputStream
        {
            $file = WebUI::assetPath('/js/pwa-sw.js');
            $this->app->response->header()->addOne(HttpHeader::SERVICE_WORKER_ALLOWED, '/');
            return new FileOutputStream($file);
        }
    }

}
