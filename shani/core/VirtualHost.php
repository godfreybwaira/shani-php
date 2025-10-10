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
         * Active configuration file from active environment
         * @var string
         */
        public readonly string $classFile;

        /**
         * Check whether application is in running state or not, if not then
         * HttpStatus::SERVICE_UNAVAILABLE is set
         * @var bool
         */
        public readonly bool $running;

        /**
         * Active application configuration profile
         * @var string
         */
        public readonly string $profile;

        public function __construct(array $config)
        {
            $this->running = $config['RUNNING'];
            $this->profile = $config['CONFIGURATION']['PROFILE'];
            $this->classFile = $config['CONFIGURATION']['CLASSFILE'];
        }
    }

}