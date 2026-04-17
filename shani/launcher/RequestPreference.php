<?php

/**
 * Description of RequestPreference
 * @author goddy
 *
 * Created on: Apr 17, 2026 at 5:17:11 PM
 */

namespace shani\launcher {

    use features\ds\map\ReadableMap;

    final class RequestPreference
    {

        public readonly string $appVersion;
        public readonly ReadableMap $vhost;
        public readonly ?string $contentVersionHeader;

        public function __construct(string $appVersion, ReadableMap $vhost, ?string $contentVersionHeader)
        {
            $this->appVersion = $appVersion;
            $this->vhost = $vhost;
            $this->contentVersionHeader = $contentVersionHeader;
        }
    }

}
