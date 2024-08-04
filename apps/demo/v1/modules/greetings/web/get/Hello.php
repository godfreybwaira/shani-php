<?php

/**
 * Description of Hello
 * @author coder
 *
 * Created on: Feb 13, 2024 at 1:50:50 PM
 */

namespace apps\demo\v1\modules\greetings\web\get {

    final class Hello
    {

        private \shani\engine\http\App $app;

        public function __construct(\shani\engine\http\App &$app)
        {
            $this->app = $app;
        }

        /**
         * Display greetings from Shani.
         */
        public function world()
        {
            //using dictionary
            $lang = $this->app->dictionary(['name' => 'user']);
            //using global style
            $this->app->template()->styles('/css/styles.css');
            //passing data to this view (greetings/views/hello/world.php)
            $this->app->render(['greeting' => $lang['hello']]);
        }
    }

}
