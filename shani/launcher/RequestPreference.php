<?php

/**
 * Description of RequestPreference
 * @author goddy
 *
 * Created on: Apr 17, 2026 at 5:17:11 PM
 */

namespace shani\launcher {

    use features\ds\map\ReadableMap;

    /**
     * Client request Configuration preferences
     */
    final class RequestPreference
    {

        /**
         * Requested application version
         * @var string
         */
        public readonly string $appVersion;

        /**
         * Loaded configuration file
         * @var string
         */
        public readonly string $configFile;

        /**
         * Virtual host configurations
         * @var ReadableMap
         */
        public readonly ReadableMap $vhost;

        /**
         * Header the client sends to request a specific version
         * @var string
         */
        public readonly string $requestVersionHeader;

        /**
         * Header we send back to tell the client which version was actually used.
         * Set to null if you don't want to return any version header
         * @var string|null
         */
        public readonly ?string $contentVersionHeader;

        public function __construct(string $appVersion, string $configFile, string $requestVersionHeader, ?string $contentVersionHeader)
        {
            $this->appVersion = $appVersion;
            $this->configFile = $configFile;
            $this->vhost = new ReadableMap(yaml_parse_file($configFile));
            $this->requestVersionHeader = $requestVersionHeader;
            $this->contentVersionHeader = $contentVersionHeader;
        }
    }

}
