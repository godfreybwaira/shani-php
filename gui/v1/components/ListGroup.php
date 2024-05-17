<?php

/**
 * Description of ListGroup
 * @author coder
 *
 * Created on: May 11, 2024 at 8:05:45 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class ListGroup extends Component
    {

        private const NAME = 'list-group', STRIPES = ['even', 'odd'];
        public const STRIPES_EVEN = 0, STRIPES_ODD = 1;

        private ?string $stripes = null;

        public function __construct()
        {
            parent::__construct('ul');
            $this->setProps([self::NAME]);
        }

        public function setStripes(int $stripes): self
        {
            $this->stripes = self::STRIPES[$stripes];
            return $this;
        }

        public function addItem(Component ...$items): self
        {
            foreach ($items as $item) {
                $list = new Component('li', false);
                $list->setProps([self::NAME . '-item'])->appendChildren($item);
            }
            return $this;
        }

        public function build(): string
        {
            if ($this->stripes !== null) {
                $this->setProps([self::NAME . '-' . $this->stripes]);
            }
            return parent::build();
        }
    }

}
