<?php

/**
 * Description of Border
 * @author coder
 *
 * Created on: Apr 17, 2025 at 6:18:29 PM
 */

namespace gui\v2\decoration {

    final class Border implements Decorator
    {

        private ?string $border;
        private readonly DimUnit $unit;

        public function __construct(DimUnit $unit = DimUnit::EM)
        {
            $this->unit = $unit;
        }

        /**
         * Set border of a component to equal size
         * @param float $size Border size
         * @param string $style Border style
         * @param string $color Border color
         */
        public function all(float $size, string $style, string $color): self
        {
            $this->border = 'border:' . $size . $this->unit->value . ' ' . $style . ' ' . $color;
            return $this;
        }

        /**
         * Set top border
         * @param float $size Border size
         * @param string $style Border style
         * @param string $color Border color
         * @return self
         */
        public function top(float $size, string $style, string $color): self
        {
            $this->border = 'border-top:' . $size . $this->unit->value . ' ' . $style . ' ' . $color;
            return $this;
        }

        /**
         * Set bottom border
         * @param float $size Border size
         * @param string $style Border style
         * @param string $color Border color
         * @return self
         */
        public function bottom(float $size, string $style, string $color): self
        {
            $this->border = 'border-bottom:' . $size . $this->unit->value . ' ' . $style . ' ' . $color;
            return $this;
        }

        /**
         * Set left border
         * @param float $size Border size
         * @param string $style Border style
         * @param string $color Border color
         * @return self
         */
        public function left(float $size, string $style, string $color): self
        {
            $this->border = 'border-left:' . $size . $this->unit->value . ' ' . $style . ' ' . $color;
            return $this;
        }

        /**
         * Set right border
         * @param float $size Border size
         * @param string $style Border style
         * @param string $color Border color
         * @return self
         */
        public function right(float $size, string $style, string $color): self
        {
            $this->border = 'border-right:' . $size . $this->unit->value . ' ' . $style . ' ' . $color;
            return $this;
        }

        public function getProperty(): ?string
        {
            if ($this->border !== null) {
                return $this->border . ';';
            }
            return null;
        }
    }

}
