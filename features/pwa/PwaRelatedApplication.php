<?php

/**
 * Description of PwaRelatedApplication
 * @author goddy
 *
 * Created on: Apr 8, 2026 at 2:29:42 PM
 */

namespace features\pwa {

    use features\pwa\enums\PwaAppPlatform;
    use features\utils\URI;

    final class PwaRelatedApplication
    {

        public readonly PwaAppPlatform $platform;
        public readonly URI $url;
        public readonly ?string $id;

        public function __construct(PwaAppPlatform $platform, URI $url, ?string $id = null)
        {
            $this->platform = $platform;
            $this->url = $url;
            $this->id = $id;
        }

        public function toArray(): array
        {
            $data = ['platform' => $this->platform->value, 'url' => $this->url->asString()];
            if ($this->id) {
                $data['id'] = $this->id;
            }
            return $data;
        }
    }

}
