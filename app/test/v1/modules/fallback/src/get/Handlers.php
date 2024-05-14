<?php

/**
 * Description of Handlers
 * @author coder
 *
 * Created on: Feb 16, 2024 at 10:26:51 AM
 */

namespace app\test\v1\modules\fallback\src\get {

    final class Handlers
    {

        private \shani\engine\http\App $app;

        public function __construct(\shani\engine\http\App &$app)
        {
            $this->app = $app;
        }

//{
//	description: "some description",
//	code: 2344,
//	status:"success|fail",
//	url:"url"
//}

        public function s400()
        {
            $this->app->response()->send('Bad request!');
        }

        public function s401()
        {
            $this->app->response()->send('Authorization failed!');
        }

        public function s404()
        {
            $this->app->response()->send('Not found!');
        }

        public function s403()
        {
            $this->app->response()->send('Forbidden!');
        }

        public function s405()
        {
            $this->app->response()->send('Not allowed!');
        }

        public function s500()
        {
            $this->app->response()->send('Server error!');
        }
    }

}
