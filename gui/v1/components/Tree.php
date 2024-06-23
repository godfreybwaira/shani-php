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

        private const NAME = 'tree';

        public function __construct()
        {
            parent::__construct('ul');
            $this->addProperty(self::NAME);
        }

        public function addItem(Component $item, bool $active = false): self
        {
            $list = new Component('li', false);
            if ($active) {
                $list->addClass('active');
            }
            $list->addProperty(self::NAME, 'title')->appendChildren($item);
            $this->appendChildren($list);
        }
    }

}
