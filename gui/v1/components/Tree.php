<?php

/**
 * Description of Tree
 * @author coder
 *
 * Created on: May 12, 2024 at 12:33:54 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class Tree extends Component
    {

        private const NAME = 'tree', TYPES = ['lines'];
        public const TYPE_1 = 0;

        private string $type = 'lines';

        public function __construct()
        {
            parent::__construct('ul');
            $this->setProps([self::NAME]);
        }

        public function addItem(ListItem $item, bool $active = false): self
        {
            if ($active) {
                $item->addClass('active');
            }
            $item->title()->setProps([self::NAME . '-title']);
            $this->appendChildren($item);
        }

        public function setType(int $type): self
        {
            $this->type = self::TYPES[$type];
            return $this;
        }

        public function build(): string
        {
            $this->setProps([self::NAME . '-type-' . $this->type]);
            return parent::build();
        }
    }

}
