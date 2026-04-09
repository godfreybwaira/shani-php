<?php

/**
 * Description of PwaIcon
 * @author goddy
 *
 * Created on: Apr 8, 2026 at 1:51:22 PM
 */

namespace gui\pwa {

    use lib\MediaType;

    final class PwaIcon
    {

        private array $data;

        public function __construct(string $src, PwaDimension $sizes, array $purposes = [PwaIconPurpose::ANY])
        {
            $this->data = [
                'src' => $src,
                'sizes' => $sizes->asString(),
                'type' => MediaType::fromFilename($src),
                'purpose' => implode(' ', array_map(fn(PwaIconPurpose $p) => $p->value, $purposes))
            ];
        }

        public function toArray(): array
        {
            return $this->data;
        }
    }

}
