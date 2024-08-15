<?php

/**
 * Description of Register
 * @author coder
 *
 * Created on: Feb 12, 2024 at 8:45:37 PM
 */

namespace apps\demo\v1\middleware {

    use shani\engine\http\App;
    use shani\engine\http\Middleware;

    final class Register extends \shani\advisors\SecurityMiddleware
    {

        public function __construct(App &$app, Middleware &$mw)
        {
            parent::__construct($app);
            $mw->on('before', fn() => Test::m1($app));
            $mw->on('before', fn() => Test::m2($app));
        }
    }

}
