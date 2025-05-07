<?php

/**
 * Description of Hello
 * @author coder
 *
 * Created on: Feb 13, 2024 at 1:50:50 PM
 */

namespace apps\demo\modules\greetings\logic\controllers\post {

    use shani\http\App;

    final class Hello
    {

        private readonly App $app;

        public function __construct(App &$app)
        {
            $this->app = $app;
        }

        public function world()
        {
            $name = $this->app->storage()->save($this->app->request->file('picha'));
            $this->app->response->setBody($name);
            $this->app->render();
        }
    }

}
