<?php

/**
 * Description of HttpServerConfig
 * @author coder
 *
 * Created on: Mar 6, 2024 at 4:06:33 PM
 */

namespace shani {

    use lib\ds\map\ReadableMap;
    use shani\core\Framework;

    final class FrameworkConfig
    {

        public readonly ReadableMap $config;

        private const MB_1 = 1048576;

        public function __construct()
        {
            self::checkFrameworkRequirements();
            $config = yaml_parse_file(Framework::DIR_CONFIG . '/framework.yml');
            /////////////////////////////////////
            $config['MAX_PAYLOAD_SIZE'] *= self::MB_1;
            $this->config = new ReadableMap($config);
            /////////////////////////////////////
            ini_set('upload_max_filesize', $config['MAX_PAYLOAD_SIZE']);
            ini_set('post_max_size', $config['MAX_PAYLOAD_SIZE'] + self::MB_1);
            ini_set('display_errors', $config['DISPLAY_ERRORS']);
            date_default_timezone_set($config['TIME_ZONE']);
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
