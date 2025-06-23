<?php

/**
 * Description of Components
 * @author coder
 *
 * Created on: Jun 12, 2025 at 1:57:16 PM
 */

namespace apps\demo\modules\shani\logic\controllers\get {

    use shani\http\App;

    final class Components
    {

        private readonly App $app;

        public function __construct(App &$app)
        {
            $this->app = $app;
        }

        public function index()
        {
            $this->app->ui()->description('Shani web framework')->title('Home Page');
            return $this->app->render();
        }

        public function all()
        {
            return $this->app->render();
        }

        public function inputs()
        {
            return $this->app->render();
        }

        public function containers()
        {
            return $this->app->render();
        }

        public function modals()
        {
            return $this->app->render();
        }

        public function toaster()
        {
            return $this->app->render();
        }

        public function loader()
        {
            return $this->app->render();
        }
    }

}
