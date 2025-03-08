<?php

/**
 * Description of Definitions
 * @author coder
 *
 * Created on: Feb 13, 2024 at 12:12:54 PM
 */

namespace shani\core {

    interface Definitions
    {

        /**
         * Default buffer size
         */
        public const BUFFER_SIZE = 1_048_576; //1MB

        /**
         * Default home function if no function name is provided on the URL
         */
        public const HOME_FUNCTION = 'index';

        /**
         * Application directory name
         */
        public const DIRNAME_APPS = '/apps';

        /**
         * Configuration directory path
         */
        public const DIR_CONFIG = SERVER_ROOT . '/config';

        /**
         * SSL files directory path
         */
        public const DIR_SSL = self::DIR_CONFIG . '/ssl';

        /**
         * Hosts directory path
         */
        public const DIR_HOSTS = self::DIR_CONFIG . '/hosts';

        /**
         * GUI directory path
         */
        public const DIR_GUI = SERVER_ROOT . '/gui';

        /**
         * Asset directory path
         */
        public const DIR_ASSETS = self::DIR_GUI . '/assets';

        /**
         * Application directory path
         */
        public const DIR_APPS = SERVER_ROOT . self::DIRNAME_APPS;

        /**
         * Minimum PHP version supported by Shani framework
         */
        public const MIN_PHP_VERSION = '8.1';

        /**
         * Mandatory extensions required by Shani framework
         */
        public const REQUIRED_EXTENSIONS = ['swoole', 'yaml', 'mbstring'];
    }

}
