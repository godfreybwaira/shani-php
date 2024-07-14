<?php

/**
 * ListPane  is a component that displays a series of content items in a
 * structured list format. It's often used to present a collection of related
 * items, such as links, text, cards, or images, in a clean and organized way.
 * @author coder
 *
 * Created on: May 11, 2024 at 8:05:45 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class ListPane extends Component
    {

        /**
         * Even list items have 'dense' background color
         */
        public const STRIPE_EVEN = 0;

        /**
         * Odd list items have 'dense' background color
         */
        public const STRIPE_ODD = 1;
        private const LIST_PANE = 0, STRIPES = 1;
        private const PROPS = [
            self::LIST_PANE => '',
            self::STRIPES => [
                self::STRIPE_EVEN => '', self::STRIPE_ODD => ''
            ]
        ];

        public function __construct()
        {
            parent::__construct('ul', self::PROPS);
            $this->addProperty(self::LIST_PANE);
        }

        /**
         * Set list stripes
         * @param int $stripes Stripe values from ListPane::STRIPES_*
         * @return self
         */
        public function setStripes(int $stripes): self
        {
            return $this->addProperty(self::STRIPES, $stripes);
        }

        /**
         * Add item(s) to a list item
         * @param Component $items
         * @return self
         */
        public function addItem(Component ...$items): self
        {
            $list = new Component('li');
            foreach ($items as $item) {
                $list->appendChildren($item);
            }
            return $this->appendChildren($list);
        }
    }

}
