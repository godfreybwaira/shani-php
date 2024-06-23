<?php

/**
 * Description of Handlers
 * @author coder
 *
 * Created on: Feb 16, 2024 at 10:26:51 AM
 */

namespace apps\test\codes\v1\modules\fallback\src\get {

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

        public function status400()
        {
            $this->app->response()->send('Bad request!');
        }

        public function status401()
        {
            $this->app->response()->send('Authorization failed!');
        }

        public function status404()
        {
            $this->app->response()->send('Not found!');
        }

        public function status403()
        {
            $this->app->response()->send('Forbidden!');
        }

        public function status405()
        {
            $this->app->response()->send('Not allowed!');
        }

        public function status500()
        {
            $this->app->response()->send('Server error!');
        }
    }

}
