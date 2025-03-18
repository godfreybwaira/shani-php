<?php

/**
 * Description of ServerConfig
 * @author coder
 *
 * Created on: Mar 6, 2024 at 4:06:33 PM
 */

namespace shani {

    use shani\core\Definitions;
    use shani\core\VirtualHost;

    final class ServerConfig
    {

        public readonly string $ip;
        public readonly string $schedulingAlgorithm;
        public readonly int $portHttp, $portHttps;
        public readonly string $sslKey, $sslCert, $timezone;
        public readonly bool $http2Enabled, $isDaemon, $showErrors;
        public readonly int $maxConnections, $maxWorkerRequests, $maxWaitTime;

        private function __construct(array $conf)
        {
            $this->ip = $conf['IP'];
            $this->portHttp = $conf['PORTS']['HTTP'];
            $this->portHttps = $conf['PORTS']['HTTPS'];
            $this->schedulingAlgorithm = $conf['SCHEDULING_ALGORITHM'];
            $this->http2Enabled = $conf['ENABLE_HTTP2'];
            $this->maxWorkerRequests = $conf['MAX_WORKER_REQUESTS'];
            $this->maxWaitTime = $conf['MAX_WAIT_TIME'];
            $this->maxConnections = $conf['MAX_CONNECTIONS'];
            $this->isDaemon = $conf['RUNAS_DAEMON'];
            $this->showErrors = $conf['DISPLAY_ERRORS'];
            $this->timezone = $conf['TIME_ZONE'];
            $this->sslCert = Definitions::DIR_SSL . $conf['SSL']['CERT'];
            $this->sslKey = Definitions::DIR_SSL . $conf['SSL']['KEY'];
        }

        public static function mime(string $extension): ?string
        {
            $mime = yaml_parse_file(Definitions::DIR_CONFIG . '/mime.yml');
            return $mime[$extension] ?? null;
        }

        public static function host(string $name): VirtualHost
        {
            $yaml = Definitions::DIR_HOSTS . '/' . $name . '.yml';
            if (is_file($yaml)) {
                return new VirtualHost(yaml_parse_file($yaml));
            }
            $alias = Definitions::DIR_HOSTS . '/' . $name . '.alias';
            if (is_file($alias)) {
                $host = file_get_contents($alias);
                return static::host(trim($host));
            }
            throw new \ErrorException('Host "' . $name . '" not found');
        }

        public static function getConfig(): self
        {
            return new self(yaml_parse_file(Definitions::DIR_CONFIG . '/server.yml'));
        }
    }

}
