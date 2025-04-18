<?php

/**
 * Represent Component spacing (padding or margin)
 * @author coder
 *
 * Created on: Apr 17, 2025 at 1:00:10 PM
 */

namespace gui\v2\decoration {

    abstract class Spacing extends Decorator
    {

        private ?string $values = null;
        private readonly string $spacing;

        protected function __construct(string $spacing, DimUnit $unit = DimUnit::EM)
        {
            parent::__construct($spacing);
            $this->spacing = $spacing;
            $this->unit = $unit;
        }

        /**
         * Set spacing on all sides (xy-axis)
         * @param float $size
         * @return void
         */
        public function all(float $size = 1): void
        {
            $this->values = $size . $this->unit->value;
        }

        /**
         * Set spacing only on some sides
         * @param float|null $top top side
         * @param float|null $right left side
         * @param float|null $bottom bottom side
         * @param float|null $left right side
         * @return void
         */
        public function only(?float $top, ?float $right, ?float $bottom, ?float $left): void
        {
            $size[] = $top !== null ? $top . $this->unit->value : 0;
            $size[] = $right !== null ? $right . $this->unit->value : 0;
            $size[] = $bottom !== null ? $bottom . $this->unit->value : 0;
            $size[] = $left !== null ? $left . $this->unit->value : 0;
            $this->values = implode(' ', $size);
        }

        /**
         * Set vertical spacing (y-axis)
         * @param float $size
         * @return void
         */
        public function vertical(float $size = 1): void
        {
            $pd = $size . $this->unit->value;
            $this->values = '0 ' . $pd . ' 0 ' . $pd . ';';
        }

        /**
         * Set horizontal spacing (x-axis)
         * @param float $size
         * @return void
         */
        public function horizontal(float $size = 1): void
        {
            $pd = $size . $this->unit->value;
            $this->values = $pd . ' 0 ' . $pd . ' 0';
        }

        public function getDecoration(): string
        {
            if ($this->values === null) {
                return null;
            }
            return '.' . $this->classId . '{' . $this->spacing . ':' . $this->values . '}';
        }

        public function remove(): self
        {
            $this->values = null;
            return $this;
        }
    }

}
