<?php

/**
 * Description of HttpServerConfig
 * @author coder
 *
 * Created on: Mar 6, 2024 at 4:06:33 PM
 */

namespace shani {

    use shani\core\Definitions;

    final class HttpServerConfig
    {

        public readonly string $serverIp, $schedulingAlgorithm;
        public readonly int $portHttp, $portHttps;
        public readonly string $sslKey, $sslCert;
        public readonly bool $http2Enabled, $isDaemon;
        public readonly int $maxConnections, $maxWorkerRequests, $maxWaitTime;

        public function __construct(array $conf)
        {
            $this->serverIp = $conf['IP'];
            $this->portHttp = $conf['PORTS']['HTTP'];
            $this->portHttps = $conf['PORTS']['HTTPS'];
            $this->schedulingAlgorithm = $conf['SCHEDULING_ALGORITHM'];
            $this->http2Enabled = $conf['ENABLE_HTTP2'];
            $this->maxWorkerRequests = $conf['MAX_WORKER_REQUESTS'];
            $this->maxWaitTime = $conf['MAX_WAIT_TIME'];
            $this->maxConnections = $conf['MAX_CONNECTIONS'];
            $this->isDaemon = $conf['RUNAS_DAEMON'];
            $this->sslCert = Definitions::DIR_SSL . $conf['SSL']['CERT'];
            $this->sslKey = Definitions::DIR_SSL . $conf['SSL']['KEY'];

            ini_set('display_errors', $conf['DISPLAY_ERRORS']);
            date_default_timezone_set($conf['TIME_ZONE']);
        }
    }

}
