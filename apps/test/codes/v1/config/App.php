<?php

/**
 * Description of App
 * @author coder
 *
 * Created on: Feb 12, 2024 at 1:42:54 PM
 */

namespace apps\test\codes\v1\config {

    use shani\engine\authorization\Authorization;
    use shani\engine\config\CSRF as CSRF_CONFIG;

    interface App
    {

        public const NAME = 'Shani Foundation Framework v1.0';
        public const DESCRIPTION = 'My app is awesome';
        public const ROOT_DIR = '/test/codes/v1';
        public const ASSET_DIR = '/test/asset';
        public const COOKIE_MAX_AGE = '2 hours';
        public const SESSION_NAME = 'sessionId';
        public const TEMPLATE_VERSION = '1.0';
        public const LANGUAGE_DEFAULT = 'sw';
        public const LANGUAGES = ['sw' => 'Kiswahili', 'en' => 'English'];
        public const CSRF = CSRF_CONFIG::PROTECTION_FLEXIBLE;
        public const AUTHORIZATION = Authorization::AUTH_SESSION;
    }

}
