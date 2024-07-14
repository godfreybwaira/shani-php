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

        private const TREE = 0, TREE_LABEL = 1;
        private const PROPS = [
            self::TREE => '',
            self::TREE_LABEL => ''
        ];

        public function __construct()
        {
            parent::__construct('ul', self::PROPS);
            $this->addProperty(self::TREE);
        }

        /**
         * Add tree child(ren)
         * @param Component $item Tree component(s)
         * @param bool $expanded If set to true, the content of a tree element will be shown
         * by default
         * @return self
         */
        public function addItem(Component $item, bool $expanded = false): self
        {
            $list = new Component('li', self::PROPS);
            $list->setActive($expanded);
            $list->addProperty(self::TREE_LABEL)->appendChildren($item);
            $this->appendChildren($list);
        }
    }

}
