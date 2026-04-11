<?php

/**
 * Description of PwaIcon
 * @author goddy
 *
 * Created on: Apr 8, 2026 at 1:51:22 PM
 */

namespace gui\pwa {

    use gui\pwa\enums\PwaIconPurpose;
    use lib\MediaType;

    final class PwaIcon
    {

        private array $data;

        public function __construct(string $src, PwaDimension $sizes, PwaIconPurpose $purpose = PwaIconPurpose::ANY)
        {
            $this->data = [
                'src' => $src,
                'sizes' => $sizes->asString(),
                'type' => MediaType::fromFilename($src),
                'purpose' => $purpose->value
            ];
        }

        public function toArray(): array
        {
            return $this->data;
        }
    }

}
