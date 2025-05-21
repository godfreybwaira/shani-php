<?php

/**
 * Represent Component spacing (padding or margin)
 * @author coder
 *
 * Created on: Apr 17, 2025 at 1:00:10 PM
 */

namespace gui\v2\decoration {


    final class Dimension implements Decorator
    {

        private ?string $width, $height;
        private readonly DimUnit $unit;

        public function __construct(DimUnit $unit = DimUnit::EM)
        {
            $this->unit = $unit;
            $this->width = null;
            $this->height = null;
        }

        /**
         * Set height and width of a component to equal size
         * @param float $size
         */
        public function all(float $size): self
        {
            return $this->only($size, $size);
        }

        /**
         * Set height or width of a component
         * @param float|null $width width size
         * @param float|null $height height size
         */
        public function only(?float $width, ?float $height): self
        {
            $this->width = $width;
            $this->height = $height;
            return $this;
        }

        public function getProperty(): ?string
        {
            $decoration = null;
            if ($this->width !== null) {
                $decoration = 'width:' . $this->width . $this->unit->value . ';';
            }
            if ($this->height !== null) {
                $decoration .= 'height:' . $this->height . $this->unit->value . ';';
            }
            return $decoration;
        }
    }

}
