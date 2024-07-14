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

        private const ACCORDION = 0, ACCORDION_ITEM = 1, ACCORDION_BODY = 2, ACCORDION_LABEL = 3;
        private const PROPS = [
            self::ACCORDION => '',
            self::ACCORDION_ITEM => '',
            self::ACCORDION_BODY => '',
            self::ACCORDION_LABEL => ''
        ];

        public function __construct()
        {
            parent::__construct('ul', self::PROPS);
            $this->addProperty(self::ACCORDION);
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
            $item->addProperty(self::ACCORDION_ITEM);
            $wrapper->addProperty(self::ACCORDION_BODY);
            $node->addProperty(self::ACCORDION_LABEL)->setAttribute('href', '#');
            $wrapper->appendChildren($body);
            $item->appendChildren($node, $wrapper);
            $item->setActive($expanded);
            if ($disabled) {
                $item->setAttribute('disabled');
            }
            return $this->appendChildren($item);
        }
    }

}
