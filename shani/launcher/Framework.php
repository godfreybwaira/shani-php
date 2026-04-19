<?php

/**
 * Description of Framework
 * @author coder
 *
 * Created on: Mar 6, 2024 at 4:06:33 PM
 */

namespace shani\launcher {

    use features\ds\map\ReadableMap;

    final class Framework
    {

        private const MB_1 = 1_048_576;
        public const NAME = 'Shani';
        public const VERSION = '2.0';
        public const SLOGAN = 'Shani yangu maanani';
        public const DEVELOPER = 'Godfrey Alphaxard Bwaira (Dr. Mbasi)';
        public const DESCRIPTION = 'an open source web framework created with &hearts; for you and I so that we can develop a fast, robust, scalable, secure web application with no hustles. Try It!';

        /**
         * Default buffer size
         */
        public const BUFFER_SIZE = self::MB_1;

        /**
         * Default home function if no function name is provided on the URL
         */
        public const HOME_FUNCTION = 'index';

        /**
         * Configuration directory path
         */
        public const DIR_CONFIG = SHANI_SERVER_ROOT . '/config';

        /**
         * SSL files directory path
         */
        public const DIR_SSL = self::DIR_CONFIG . '/ssl';

        /**
         * Hosts directory path
         */
        public const DIR_HOSTS = self::DIR_CONFIG . '/vhosts';

        /**
         * GUI directory path
         */
        public const DIR_GUI = SHANI_SERVER_ROOT . '/gui';

        /**
         * Storage directory path
         */
        public const DIR_STORAGE = SHANI_SERVER_ROOT . '/bucket';

        /**
         * Storage directory path
         */
        public const DIR_SERVER_STORAGE = self::DIR_STORAGE . '/.svr';

        /**
         * Asset directory path
         */
        public const DIR_ASSETS = self::DIR_GUI . '/assets';

        /**
         * Application directory path
         */
        public const DIR_APPS = SHANI_SERVER_ROOT . '/apps';

        /**
         * Minimum PHP version supported by Shani framework
         */
        public const MIN_PHP_VERSION = '8.1';

        /**
         * Mandatory extensions required by Shani framework
         */
        public const REQUIRED_EXTENSIONS = ['yaml'];

        public readonly ReadableMap $config;

        public function __construct()
        {
            self::checkFrameworkRequirements();
            $config = yaml_parse_file(Framework::DIR_CONFIG . '/framework.yml');
            /////////////////////////////////////
            $config['max_payload_size'] *= self::MB_1;
            $this->config = new ReadableMap($config);
            /////////////////////////////////////
            ini_set('upload_max_filesize', $config['max_payload_size']);
            ini_set('post_max_size', $config['max_payload_size'] + self::MB_1);
            ini_set('display_errors', $config['display_errors']);
            date_default_timezone_set($config['time_zone']);
        }

        private static function checkFrameworkRequirements()
        {
            if (version_compare(Framework::MIN_PHP_VERSION, PHP_VERSION) >= 0) {
                echo 'PHP version ' . Framework::MIN_PHP_VERSION . ' or higher is required' . PHP_EOL;
                exit(1);
            }
            foreach (Framework::REQUIRED_EXTENSIONS as $extension) {
                if (!extension_loaded($extension)) {
                    echo 'Please install PHP ' . $extension . ' extension' . PHP_EOL;
                    exit(1);
                }
            }
        }
    }

}
