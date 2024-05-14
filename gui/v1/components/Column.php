<?php

/**
 * Description of Column
 * @author coder
 *
 * Created on: May 12, 2024 at 11:01:53 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class Column extends Component
    {

        private const NAME = 'col';

        private ?int $size = null;

        public function __construct()
        {
            parent::__construct('div');
            $this->setProps([self::NAME]);
        }

        public function setWidth(int $size, int $width): self
        {
            $this->size = parent::SIZES[$size] . '-' . $width;
            return $this;
        }

        public function setStretch(): self
        {
            return $this->setProps([self::NAME . '-stretch']);
        }

        public function build(): string
        {
            if ($this->size !== null) {
                return $this->setProps(['width-' . $this->size]);
            }
            return parent::build();
        }
    }

}
