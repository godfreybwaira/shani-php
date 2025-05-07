<?php

/**
 * Description of HttpServerConfig
 * @author coder
 *
 * Created on: Mar 6, 2024 at 4:06:33 PM
 */

namespace shani {

    use shani\core\Definitions;

    final class FrameworkConfig
    {

        public readonly string $serverIp, $sslKey, $sslCert;
        public readonly int $httpPort, $httpsPort, $payloadSize;

        private const MB_1 = 1048576;

        public function __construct()
        {
            self::checkFrameworkRequirements();
            $config = yaml_parse_file(Definitions::DIR_CONFIG . '/framework.yml');
            /////////////////////////////////////
            $this->serverIp = $config['IP'];
            $this->httpPort = $config['PORTS']['HTTP'];
            $this->httpsPort = $config['PORTS']['HTTPS'];
            $this->sslCert = Definitions::DIR_SSL . $config['SSL']['CERT'];
            $this->sslKey = Definitions::DIR_SSL . $config['SSL']['KEY'];
            $this->payloadSize = $config['MAX_PAYLOAD_SIZE'] * self::MB_1;
            /////////////////////////////////////
            ini_set('display_errors', $config['DISPLAY_ERRORS']);
            date_default_timezone_set($config['TIME_ZONE']);
        }

        private static function checkFrameworkRequirements()
        {
            if (version_compare(Definitions::MIN_PHP_VERSION, PHP_VERSION) >= 0) {
                fwrite(STDERR, 'PHP version ' . Definitions::MIN_PHP_VERSION . ' or higher is required' . PHP_EOL);
                exit(1);
            }
            foreach (Definitions::REQUIRED_EXTENSIONS as $extension) {
                if (!extension_loaded($extension)) {
                    fwrite(STDERR, 'Please install PHP ' . $extension . ' extension' . PHP_EOL);
                    exit(1);
                }
            }
        }
    }

}
