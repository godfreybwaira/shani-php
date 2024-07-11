<?php

/**
 * Description of Register
 * @author coder
 *
 * Created on: Feb 12, 2024 at 8:45:37 PM
 */

namespace apps\demo\codes\v1\middleware {

    use shani\engine\http\App;
    use shani\engine\http\Middleware;

    final class Register
    {

        public static function exec(App &$app, Middleware &$mw)
        {
            $mw->on('before', fn() => Test::m1($app));
            $mw->on('before', fn() => Test::m2($app));
        }
    }

}
