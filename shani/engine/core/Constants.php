<?php

/**
 * Description of Constants
 * @author coder
 *
 * Created on: Feb 13, 2024 at 12:12:54 PM
 */

namespace shani\engine\core {

    interface Constants
    {

        /**
         * Default home function if no function name is provided on URL
         */
        public const HOME_FUNCTION = 'index';

        /**
         * Application directory name
         */
        public const DIRNAME_APPS = '/apps';

        /**
         * Configuration directory absolute path
         */
        public const DIR_CONFIG = SERVER_ROOT . '/config';

        /**
         * SSL files directory
         */
        public const DIR_SSL = self::DIR_CONFIG . '/ssl';

        /**
         * Hosts directory absolute path
         */
        public const DIR_HOSTS = self::DIR_CONFIG . '/hosts';

        /**
         * GUI directory absolute path
         */
        public const DIR_GUI = SERVER_ROOT . '/gui';

        /**
         * Application directory absolute path
         */
        public const DIR_APPS = SERVER_ROOT . self::DIRNAME_APPS;

        /**
         * Minimum PHP version supported by Shani framework
         */
        public const MIN_PHP_VERSION = '7.4';

        /**
         * Mandatory extensions required by Shani framework
         */
        public const REQUIRED_EXTENSIONS = ['swoole', 'yaml'];
    }

}
