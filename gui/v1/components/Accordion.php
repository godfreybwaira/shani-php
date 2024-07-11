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

        private const NAME = 'accordion';

        public function __construct()
        {
            parent::__construct('ul');
            $this->addProperty(self::NAME);
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
            $item = new Component('li', false);
            $node = new Component('a', false);
            $node->setContent($label);
            $wrapper = new Component('div', false);
            $item->addProperty(self::NAME . '-item');
            $wrapper->addProperty(self::NAME . '-body');
            $node->addProperty(self::NAME . '-label')->setAttribute('href', '#');
            $wrapper->appendChildren($body);
            $item->appendChildren($node, $wrapper);
            if ($expanded) {
                $item->addClass('expanded');
            }
            if ($disabled) {
                $item->setAttribute('disabled');
            }
            return $this->appendChildren($item);
        }
    }

}
