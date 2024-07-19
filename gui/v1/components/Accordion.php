<?php

/**
 * Accordion is a graphical control element comprising a vertically stacked list
 * of items, such as labels or thumbnails. Each item can be "expanded" or "stretched"
 * to reveal the content associated with that item.
 * @author coder
 *
 * Created on: May 13, 2024 at 11:47:22 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class Accordion extends Component
    {

        /**
         * Open the current element and close all other. This is the default behavior
         */
        public const OPEN_BEHAVIOR_CLOSE_OTHER = 0;

        /**
         * Open the current element without considering the state of other element.
         */
        public const OPEN_BEHAVIOR_OPEN_ALL = 1;

        /**
         * This will disable the opening behavior. All elements are open by default.
         */
        public const OPEN_BEHAVIOR_NONE = 2;
        ///////////////////
        private const ACCORDION = 0, ACCORDION_ITEM = 1, ACCORDION_BODY = 2, ACCORDION_LABEL = 3;
        private const OPEN_BEHAVIOR = 4;
        private const PROPS = [
            self::ACCORDION => '',
            self::ACCORDION_ITEM => '',
            self::ACCORDION_BODY => '',
            self::ACCORDION_LABEL => '',
            self::OPEN_BEHAVIOR => [
                self::OPEN_BEHAVIOR_CLOSE_OTHER => '',
                self::OPEN_BEHAVIOR_OPEN_ALL => '',
                self::OPEN_BEHAVIOR_NONE => ''
            ]
        ];

        public function __construct(int $openBehavior = null)
        {
            parent::__construct('ul', self::PROPS);
            $this->setOpenBehavior($openBehavior ?? self::OPEN_BEHAVIOR_CLOSE_OTHER);
            $this->addStyle(self::ACCORDION);
        }

        /**
         * Add accordion element as child item to accordion
         * @param string $label Display accordion title name
         * @param Component $body Accordion content
         * @param bool $expanded If set to true, the content of accordion element will be shown
         * by default
         * @param bool $disabled If set tot true, the accordion element will be disabled
         * @return self
         */
        public function addItem(string $label, Component $body, bool $expanded = false, bool $disabled = false): self
        {
            $item = new Component('li', self::PROPS);
            $node = new Component('a', self::PROPS);
            $wrapper = new Component('div', self::PROPS);
            $node->setContent($label);
            $item->addStyle(self::ACCORDION_ITEM);
            $wrapper->addStyle(self::ACCORDION_BODY);
            $node->addStyle(self::ACCORDION_LABEL)->setAttribute('href', '#');
            $wrapper->appendChildren($body);
            $item->appendChildren($node, $wrapper);
            $item->setActive($expanded);
            if ($disabled) {
                $item->setAttribute('disabled');
            }
            return $this->appendChildren($item);
        }

        /**
         * Set open behavior for this component
         * @param int $openBehavior Open behavior set using Accordion::OPEN_BEHAVIOR_*
         * @return self
         */
        public function setOpenBehavior(int $openBehavior): self
        {
            return $this->addStyle(self::OPEN_BEHAVIOR, $openBehavior);
        }
    }

}
