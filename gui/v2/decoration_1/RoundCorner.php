<?php

/**
 * Description of RoundCorner
 * @author coder
 *
 * Created on: Apr 17, 2025 at 6:18:29 PM
 */

namespace gui\v2\decoration {

    final class RoundCorner implements Decorator
    {

        private ?string $radius;
        private readonly DimUnit $unit;

        public function __construct(DimUnit $unit = DimUnit::EM)
        {
            $this->unit = $unit;
        }

        /**
         * Set border radius of a component to equal size
         * @param float $size
         */
        public function all(float $size): self
        {
            $this->radius = $size . $this->unit->value;
            return $this;
        }

        /**
         * Set top border radius
         * @param float|null $left Left corner
         * @param float|null $right Right corner
         * @return self
         */
        public function top(?float $left, ?float $right): self
        {
            $size[] = $left !== null ? $left . $this->unit->value : 0;
            $size[] = $right !== null ? $right . $this->unit->value : 0;
            $size[] = 0;
            $size[] = 0;
            $this->radius = implode(' ', $size);
            return $this;
        }

        /**
         * Set bottom border radius
         * @param float|null $left Left corner
         * @param float|null $right Right corner
         * @return self
         */
        public function bottom(?float $left, ?float $right): self
        {
            $size[] = 0;
            $size[] = 0;
            $size[] = $left !== null ? $left . $this->unit->value : 0;
            $size[] = $right !== null ? $right . $this->unit->value : 0;
            $this->radius = implode(' ', $size);
            return $this;
        }

        /**
         * Set left border radius
         * @param float|null $top Top corner
         * @param float|null $bottom Bottom corner
         * @return self
         */
        public function left(?float $top, ?float $bottom): self
        {
            $size[] = $top !== null ? $top . $this->unit->value : 0;
            $size[] = 0;
            $size[] = 0;
            $size[] = $bottom !== null ? $bottom . $this->unit->value : 0;
            $this->radius = implode(' ', $size);
            return $this;
        }

        /**
         * Set right border radius
         * @param float|null $top Top corner
         * @param float|null $bottom Bottom corner
         * @return self
         */
        public function right(?float $top, ?float $bottom): self
        {
            $size[] = 0;
            $size[] = $top !== null ? $top . $this->unit->value : 0;
            $size[] = $bottom !== null ? $bottom . $this->unit->value : 0;
            $size[] = 0;
            $this->radius = implode(' ', $size);
            return $this;
        }

        public function only(?float $topLeft, ?float $topRight, ?float $bottomLeft, ?float $bottomRight): self
        {
            $size[] = $topLeft !== null ? $topLeft . $this->unit->value : 0;
            $size[] = $topRight !== null ? $topRight . $this->unit->value : 0;
            $size[] = $bottomLeft !== null ? $bottomLeft . $this->unit->value : 0;
            $size[] = $bottomRight !== null ? $bottomRight . $this->unit->value : 0;
            $this->radius = implode(' ', $size);
            return $this;
        }

        public function getProperty(): ?string
        {
            if ($this->radius !== null) {
                return 'border-radius:' . $this->radius . ';';
            }
            return null;
        }
    }

}
