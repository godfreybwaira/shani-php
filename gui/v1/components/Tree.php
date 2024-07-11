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

        /**
         * Add tree child(ren)
         * @param Component $item Tree component(s)
         * @param bool $expanded If set to true, the content of a tree element will be shown
         * by default
         * @return self
         */
        public function addItem(Component $item, bool $expanded = false): self
        {
            $list = new Component('li', false);
            if ($expanded) {
                $list->addClass('expanded');
            }
            $list->addProperty(self::NAME, 'label')->appendChildren($item);
            $this->appendChildren($list);
        }
    }

}
