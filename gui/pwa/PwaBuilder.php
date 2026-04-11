<?php

/**
 * Description of PwaBuilder
 * @author goddy
 *
 * Created on: Apr 9, 2026 at 4:51:16 PM
 */

namespace gui\pwa {

    final class PwaBuilder
    {

        public readonly string $manifest;
        public readonly string $serviceWorker;
        public readonly string $scope;

        public function __construct(string $manifest, string $serviceWorker, string $scope = '/')
        {
            $this->scope = $scope;
            $this->manifest = $manifest;
            $this->serviceWorker = $serviceWorker;
        }
    }

}
