<?php

/**
 * Description of Hello
 * @author coder
 *
 * Created on: Feb 13, 2024 at 1:50:50 PM
 */

namespace apps\demo\modules\salamu\logic\controllers\post {

    use shani\http\App;

    final class One
    {

        private readonly App $app;

        public function __construct(App &$app)
        {
            $this->app = $app;
        }

        /**
         * Display greetings from Shani.
         */
        public function number1()
        {

        }

        public function number2()
        {

        }
    }

}
