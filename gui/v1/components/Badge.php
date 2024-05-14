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

        private const NAME = 'badge';

        private ?string $position = null;

        public function __construct(string $text = null)
        {
            parent::__construct('div', $text);
            $this->setProps([self::NAME]);
        }

        public function setPosition(int $position): self
        {
            $this->position = parent::POSITIONS[$position];
            return $this;
        }

        public function setParent(Component &$parent): self
        {
            $parent->setProps(['relative-pos'])->appendChildren($this);
            return $this;
        }

        public function build(): string
        {
            if ($this->position !== null) {
                $this->setProps([$this->position]);
            }
            return parent::build();
        }
    }

}
