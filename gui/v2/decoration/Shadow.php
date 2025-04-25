<?php

/**
 * Description of Shadow
 * @author coder
 *
 * Created on: Apr 17, 2025 at 6:46:47 PM
 */

namespace gui\v2\decoration {

    final class Shadow implements Decorator
    {

        private readonly DimUnit $unit;
        private string $color = 'black';
        private ?float $spread = null, $blur = null;
        private ?string $shadow = null, $inset = null;

        public function __construct(DimUnit $unit = DimUnit::EM)
        {
            $this->unit = $unit;
        }

        /**
         * Set box shadow on X and Y axis
         * @param float $size
         * @return self
         */
        public function all(float $size): self
        {
            $this->shadow = $size . $this->unit->value . ' ' . $size . $this->unit->value;
            return $this;
        }

        /**
         * Set box shadow on either X and Y axis
         * @param float $size
         * @return self
         */
        public function only(?float $horizontal, ?float $vertical): self
        {
            $size[] = $horizontal !== null ? $horizontal . $this->unit->value : 0;
            $size[] = $vertical !== null ? $vertical . $this->unit->value : 0;
            $this->shadow = implode(' ', $size);
            return $this;
        }

        /**
         * Set box shadow blur
         * @param float $size
         * @return self
         */
        public function blur(float $size): self
        {
            $this->blur = ' ' . $size;
            return $this;
        }

        /**
         * Set box shadow spread
         * @param float $size
         * @return self
         */
        public function spread(float $size): self
        {
            $this->spread = ' ' . $size;
            return $this;
        }

        /**
         * Set box shadow color
         * @param string $color
         * @return self
         */
        public function color(string $color): self
        {
            $this->color = ' ' . $color;
            return $this;
        }

        /**
         * Set box shadow inset
         * @param bool $inset
         * @return self
         */
        public function inset(bool $inset): self
        {
            $this->inset = $inset ? 'inset ' : null;
            return $this;
        }

        public function getProperty(): ?string
        {
            if ($this->shadow !== null) {
                $shadow = $this->inset . $this->shadow . $this->blur . $this->spread . $this->color;
                return 'box-shadow:' . $shadow . ';';
            }
            return null;
        }
    }

}
