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
         * Virtual host configurations
         * @var ReadableMap
         */
        public readonly ReadableMap $vhost;
        public readonly string $requestVersionHeader;

        /**
         * Content version response header.
         * @var string|null
         */
        public readonly ?string $contentVersionHeader;

        public function __construct(string $appVersion, ReadableMap $vhost, string $requestVersionHeader, ?string $contentVersionHeader)
        {
            $this->appVersion = $appVersion;
            $this->vhost = $vhost;
            $this->requestVersionHeader = $requestVersionHeader;
            $this->contentVersionHeader = $contentVersionHeader;
        }
    }

}
