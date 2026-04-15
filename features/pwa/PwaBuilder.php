<?php

/**
 * Description of PwaBuilder
 * @author goddy
 *
 * Created on: Apr 9, 2026 at 4:51:16 PM
 */

namespace features\pwa {

    use features\utils\URI;

    final class PwaBuilder
    {

        public readonly URI $manifest;
        public readonly URI $serviceWorker;
        public readonly string $scope;

        public function __construct(URI $manifest, URI $serviceWorker, string $scope = '/')
        {
            $this->scope = $scope;
            $this->manifest = $manifest;
            $this->serviceWorker = $serviceWorker;
        }
    }

}
