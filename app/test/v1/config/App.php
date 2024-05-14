<?php

/**
 * Description of App
 * @author coder
 *
 * Created on: Feb 12, 2024 at 1:42:54 PM
 */

namespace app\test\v1\config {

    interface App
    {

        public const NAME = 'Shani Foundation Framework v1.0';
        public const DEVELOPMENT = true;
        public const ROOT_DIR = '/test/v1';
        public const COOKIE_MAX_AGE = '2 hours';
        public const SESSION_NAME = 'sessionId';
        public const LANGUAGE_DEFAULT = 'sw';
        public const LANGUAGES = ['sw' => 'Kiswahili', 'en' => 'English'];
        public const CSRF = \shani\engine\config\CSRF::PROTECTION_FLEXIBLE;
        public const TEMPLATE_VERSION = '1.0';
    }

}
