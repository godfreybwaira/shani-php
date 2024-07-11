<?php

/**
 * ListPane  is a component that displays a series of content items in a
 * structured list format. It's often used to present a collection of related
 * items, such as links, text, or images, in a clean and organized way.
 * @author coder
 *
 * Created on: May 11, 2024 at 8:05:45 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class ListPane extends Component
    {

        private const NAME = 'list-pane', STRIPES = ['even', 'odd'];
        public const STRIPES_EVEN = 0, STRIPES_ODD = 1;

        public function __construct()
        {
            parent::__construct('ul');
            $this->addProperty(self::NAME);
        }

        /**
         * Set list stripes
         * @param int $stripes Stripe values from ListPane::STRIPES_*
         * @return self
         */
        public function setStripes(int $stripes): self
        {
            return $this->addProperty(self::NAME . '-stripes', self::STRIPES[$stripes]);
        }

        /**
         * Add item(s) to a list item
         * @param Component $items
         * @return self
         */
        public function addItem(Component ...$items): self
        {
            $list = new Component('li', false);
            foreach ($items as $item) {
                $list->appendChildren($item);
            }
            return $this->appendChildren($list);
        }
    }

}
