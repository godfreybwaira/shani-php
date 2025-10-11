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
         * Active application configuration profile name
         * @var string
         */
        public readonly string $profile;

        public function __construct(array $config)
        {
            $this->profile = $config['CONFIGURATION']['PROFILE'];
            $this->classFile = $config['CONFIGURATION']['CLASSFILE'];
        }
    }

}