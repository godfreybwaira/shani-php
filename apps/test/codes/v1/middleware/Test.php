<?php

/**
 * Description of Test
 * @author coder
 *
 * Created on: Feb 13, 2024 at 11:02:00 AM
 */

namespace apps\test\codes\v1\middleware {

    final class Test
    {

        public static function before(\shani\engine\http\App $app): array
        {
            return ['before1' => 'imefanikiwa'];
        }

        public static function after(\shani\engine\http\App $app): array
        {
            return ['after1' => 'hii pia imefanikiwa'];
        }
    }

}
