<?php

/**
 * Description of Badge
 * @author coder
 *
 * Created on: May 12, 2024 at 10:17:47 AM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class Badge extends Component
    {

        private bool $wrapped = false;
        private ?Component $parent = null;
        private ?string $position = null;

        public function __construct(string $text = null)
        {
            parent::__construct('div', $text);
            $this->setProps(['badge']);
        }

        public function setPosition(int $position): self
        {
            $this->position = parent::POSITIONS[$position];
            return $this;
        }

        public function setParent(Component $parent): self
        {
            $this->parent = $parent;
            $this->parent->setProps(['pos-relative'])->appendChildren($this);
            return $this;
        }

        public function build(): string
        {
            if ($this->parent !== null && !$this->wrapped) {
                $this->wrapped = true;
                return $this->parent->build();
            }
            $this->setProps(['badge-' . $this->position]);
            return parent::build();
        }
    }

}
