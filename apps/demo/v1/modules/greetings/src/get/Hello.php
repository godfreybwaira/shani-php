<?php

/**
 * Description of Hello
 * @author coder
 *
 * Created on: Feb 13, 2024 at 1:50:50 PM
 */

namespace apps\demo\v1\modules\greetings\src\get {

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
            $lang = $this->app->dictionary(['name' => 'user']);
            $this->app->template()->styles('/css/styles.css');
            $this->app->render(['greeting' => $lang['hello']]);
        }
    }

}
