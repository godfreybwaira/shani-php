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

        /**
         * Display greetings from Shani.
         */
        public function world()
        {
            $file = $this->app->request->file('picha');
            $path = $this->app->storage()->save($file);
            echo $this->app->storage()->url($path);
            echo PHP_EOL;
            $path = $this->app->storage()->savePrivate($file);
            echo $this->app->storage()->url($path);
            echo PHP_EOL;
            $path = $this->app->storage()->saveProtect($file);
            echo $this->app->storage()->url($path);
            $this->app->send();
        }
    }

}
