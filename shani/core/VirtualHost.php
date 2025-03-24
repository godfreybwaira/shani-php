<?php

/**
 * Description of VirtualHost
 * @author coder
 *
 * Created on: Mar 8, 2025 at 4:29:45 PM
 */

namespace shani\core {

    final class VirtualHost
    {

        /**
         * Application running environments
         * @var array
         */
        public readonly array $env;

        /**
         * Application active environment
         * @var string
         */
        public readonly string $activeEnv;

        /**
         * Check whether application is in running state or not, if not then
         * HttpStatus::SERVICE_UNAVAILABLE is set
         * @var bool
         */
        public readonly bool $running;

        /**
         * Active configuration file from active environment
         * @var string
         */
        public readonly string $configFile;

        /**
         * Whether to cache the current configuration or not
         * @var string
         */
        public readonly string $cache;

        public function __construct(array $config)
        {
            $this->env = $config['ENVIRONMENTS'];
            $this->activeEnv = $config['ACTIVE_ENVIRONMENT'];
            $this->configFile = $this->env[$this->activeEnv];
            $this->running = $config['RUNNING'];
            $this->cache = $config['CACHE'];
        }
    }

}