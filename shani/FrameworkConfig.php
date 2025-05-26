<?php

/**
 * Description of HttpServerConfig
 * @author coder
 *
 * Created on: Mar 6, 2024 at 4:06:33 PM
 */

namespace shani {

    use shani\core\Framework;

    final class FrameworkConfig
    {

        public readonly string $serverIp, $sslKey, $sslCert;
        public readonly int $httpPort, $httpsPort, $payloadSize;
        public readonly bool $showErrors;

        private const MB_1 = 1048576;

        public function __construct()
        {
            self::checkFrameworkRequirements();
            $config = yaml_parse_file(Framework::DIR_CONFIG . '/framework.yml');
            /////////////////////////////////////
            $this->serverIp = $config['IP'];
            $this->httpPort = $config['PORTS']['HTTP'];
            $this->httpsPort = $config['PORTS']['HTTPS'];
            $this->showErrors = $config['DISPLAY_ERRORS'];
            $this->sslCert = Framework::DIR_SSL . $config['SSL']['CERT'];
            $this->sslKey = Framework::DIR_SSL . $config['SSL']['KEY'];
            $this->payloadSize = $config['MAX_PAYLOAD_SIZE'] * self::MB_1;
            /////////////////////////////////////
            ini_set('upload_max_filesize', $this->payloadSize);
            ini_set('post_max_size', $this->payloadSize + self::MB_1);
            ini_set('display_errors', $this->showErrors);
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
