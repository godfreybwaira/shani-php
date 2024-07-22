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
    use gui\v1\Style;

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
        ////////////////////
        private const LIST_PANE = 0, LIST_STRIPES = 1, LIST_ALIGN = 2;
        private const PROPS = [
            self::LIST_PANE => '',
            self::LIST_STRIPES => [
                self::STRIPE_EVEN => '', self::STRIPE_ODD => ''
            ],
            self::LIST_ALIGN => [
                Style::ALIGN_VERTICAL => '', Style::ALIGN_HORIZONTAL => ''
            ]
        ];

        public function __construct()
        {
            parent::__construct('ul', self::PROPS);
            $this->addStyle(self::LIST_PANE);
            $this->setAlignment(Style::ALIGN_VERTICAL);
        }

        /**
         * Set list stripes
         * @param int $stripes Stripe values from ListPane::STRIPES_*
         * @return self
         */
        public function setStripes(int $stripes): self
        {
            return $this->addStyle(self::LIST_STRIPES, $stripes);
        }

        /**
         * Add item(s) to a list item
         * @param Component $items
         * @return self
         */
        public function addItem(Component ...$items): self
        {
            foreach ($items as $item) {
                $list = new Component('li');
                $this->appendChildren($list->appendChildren($item));
            }
            return $this;
        }

        public function setAlignment(?int $alignment): self
        {
            return $this->addStyle(self::LIST_ALIGN, $alignment ?? Style::ALIGN_VERTICAL);
        }
    }

}
