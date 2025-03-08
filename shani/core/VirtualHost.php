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
         * Check whether application is in running state or not. A programmer should
         * implements the logic on application running state, otherwise this configuration
         * has no effect.
         * @var bool
         */
        public readonly bool $running;
        public readonly string $config;

        public function __construct(array $config)
        {
            $this->env = $config['ENVIRONMENTS'];
            $this->activeEnv = $config['ACTIVE_ENVIRONMENT'];
            $this->config = $this->env[$this->activeEnv];
            $this->running = $config['RUNNING'];
        }
    }

}