<?php

/**
 * Description of Path
 * @author coder
 *
 * Created on: Feb 16, 2024 at 12:11:36 PM
 */

namespace app\test\v1\config {

    interface Path
    {

        public const MODULES = '/modules';
        public const VIEWS = '/views';
        public const LANGUAGE = '/lang';
        public const SOURCE = '/src';
        public const BREADCRUMB = '/breadcrumb';
        public const BREADCRUMB_METHOD = '/functions';
    }

}
