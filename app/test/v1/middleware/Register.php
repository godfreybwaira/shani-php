<?php

/**
 * Description of Register
 * @author coder
 *
 * Created on: Feb 12, 2024 at 8:45:37 PM
 */

namespace app\test\v1\middleware {

    final class Register {

        public static function exec(\shani\engine\http\App &$app, \shani\engine\middleware\Register &$mw) {
            $mw->on('before', fn() => Test::before($app));
            $mw->on('before', fn() => Test::after($app));
        }
    }

}
