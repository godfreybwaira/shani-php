<?php

/**
 * Description of PwaDimension
 * @author goddy
 *
 * Created on: Apr 9, 2026 at 1:28:14 PM
 */

namespace features\pwa {

    final class PwaDimension
    {

        private int $width, $height;
        private ?string $any = null;

        public function __construct(int $width = null, int $height = null)
        {
            if ($width === null && $height === null) {
                $this->any = 'any';
            } else {
                $this->width = $width;
                $this->height = $height ?? $width;
            }
        }

        public function asString(): string
        {
            return $this->any ?? $this->width . 'x' . $this->height;
        }
    }

}
