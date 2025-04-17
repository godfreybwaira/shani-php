<?php

/**
 * Represent Component spacing (padding or margin)
 * @author coder
 *
 * Created on: Apr 17, 2025 at 1:00:10 PM
 */

namespace gui\v2\decoration {


    final class Dimension extends Decorator
    {

        private ?string $width, $height;
        private readonly DimUnit $unit;

        public function __construct(DimUnit $unit = DimUnit::EM)
        {
            parent::__construct('dimension');
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
            return $this->width($size)->height($size);
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

        /**
         * Set width of a component
         * @param float $size
         */
        public function width(float $size): self
        {
            $this->width = $size;
            return $this;
        }

        /**
         * Set height of a component
         * @param float $size
         */
        public function height(float $size): self
        {
            $this->height = $size;
            return $this;
        }

        public function getDecoration(): ?string
        {
            $decoration = null;
            if ($this->width !== null) {
                $decoration = 'width:' . $this->width . $this->unit->value . ';';
            }
            if ($this->height !== null) {
                $decoration .= 'height:' . $this->height . $this->unit->value . ';';
            }
            if ($decoration !== null) {
                return '.' . $this->classId . '{' . $decoration . '}';
            }
            return null;
        }

        public function remove(): self
        {
            $this->width = null;
            $this->height = null;
            return $this;
        }
    }

}
