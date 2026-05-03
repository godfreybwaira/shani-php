<?php

/**
 * Description of Framework
 * @author coder
 *
 * Created on: Mar 6, 2024 at 4:06:33 PM
 */

namespace shani\launcher {

    use features\ds\map\ReadableMap;

    /**
     * Core configuration class for the Shani framework.
     *
     * The Framework class defines global constants, directory paths, and
     * environment requirements for the application. It ensures that the
     * framework is properly initialized and that the runtime environment
     * meets minimum requirements.
     *
     * Responsibilities:
     * - Define framework metadata (name, version, slogan, developer, description)
     * - Provide directory paths for configuration, SSL, hosts, GUI, storage, assets, and apps
     * - Enforce minimum PHP version and required extensions
     * - Load and parse framework configuration from YAML
     * - Apply runtime settings (upload limits, error display, timezone)
     *
     * By default:
     * - Minimum PHP version → 8.1
     * - Required extensions → ['yaml']
     * - Buffer size → 1 MB
     * - Home function → 'index'
     */
    final class Framework
    {

        private const MB_1 = 1_048_576;

        /** Framework name */
        public const NAME = 'Shani';

        /** Framework version */
        public const VERSION = '2.0';

        /** Framework slogan */
        public const SLOGAN = 'Shani yangu maanani';

        /** Framework developer */
        public const DEVELOPER = 'Godfrey Alphaxard Bwaira (Dr. Mbasi)';

        /** Framework description */
        public const DESCRIPTION = 'an open source web framework created with &hearts; for you and I so that we can develop a fast, robust, scalable, secure web application with no hustles. Try It!';

        /** Default buffer size */
        public const BUFFER_SIZE = self::MB_1;

        /** Default home function if no function name is provided on the URL */
        public const HOME_FUNCTION = 'index';

        /** Configuration directory path */
        public const DIR_CONFIG = SHANI_SERVER_ROOT . '/config';

        /** Configuration directory path */
        public const DIR_SHANI = SHANI_SERVER_ROOT . '/shani';

        /** SSL files directory path */
        public const DIR_SSL = self::DIR_CONFIG . '/ssl';

        /** Hosts directory path */
        public const DIR_HOSTS = self::DIR_CONFIG . '/vhosts';

        /** GUI directory path */
        public const DIR_GUI = SHANI_SERVER_ROOT . '/gui';

        /** Storage directory path */
        public const DIR_STORAGE = SHANI_SERVER_ROOT . '/bucket';

        /** Server storage directory path */
        public const DIR_SERVER_STORAGE = self::DIR_STORAGE . '/.svr';

        /** Asset directory path */
        public const DIR_ASSETS = self::DIR_GUI . '/assets';

        /** Application directory path */
        public const DIR_APPS = SHANI_SERVER_ROOT . '/apps';

        /** Minimum PHP version supported by Shani framework */
        public const MIN_PHP_VERSION = '8.1';

        /** Mandatory extensions required by Shani framework */
        public const REQUIRED_EXTENSIONS = ['yaml'];

        /** Framework features path */
        public const DIR_FEATURES = SHANI_SERVER_ROOT . '/features';

        /**
         * Framework configuration map.
         *
         * @var ReadableMap
         */
        public readonly ReadableMap $config;

        /**
         * Constructor for Framework.
         *
         * Initializes framework configuration, checks requirements, and applies runtime settings.
         */
        public function __construct()
        {
            self::checkFrameworkRequirements();
            $config = yaml_parse_file(Framework::DIR_CONFIG . '/framework.yml');

            // Convert payload size to bytes
            $config['max_payload_size'] *= self::MB_1;
            $this->config = new ReadableMap($config);

            // Apply runtime settings
            ini_set('upload_max_filesize', $config['max_payload_size']);
            ini_set('post_max_size', $config['max_payload_size'] + self::MB_1);
            ini_set('display_errors', $config['display_errors']);
            date_default_timezone_set($config['time_zone']);
        }

        /**
         * Check framework requirements (PHP version and extensions).
         *
         * Exits with error message if requirements are not met.
         *
         * @return void
         */
        private static function checkFrameworkRequirements(): void
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
