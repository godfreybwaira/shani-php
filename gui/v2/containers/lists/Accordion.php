<?php

/**
 * Description of Accordion
 * @author coder
 *
 * Created on: May 23, 2025 at 11:38:14 PM
 */

namespace gui\v2\containers\lists {

    use gui\v2\Component;

    final class Accordion extends Component
    {

        private const CSS_CLASS = 'accordion';

        /**
         * Create Accordion
         * @param bool $collapse If true, then all the items are collapsed except
         * the one with class active.
         */
        protected function __construct(bool $collapse = true)
        {
            parent::__construct('ul');
            $this->classList->addAll([self::CSS_CLASS, 'borders']);
            if ($collapse) {
                $this->attribute->addOne('ui-collapse', 'true');
            }
        }

        /**
         * Add an accordion item
         * @param Component $title Item title
         * @param Component $body Item body
         * @param bool $active Whether this item is active or not
         * @return self
         */
        public function addItem(Component $title, Component $body, bool $active = false): self
        {
            $list = new Component('li');
            $title->classList->addOne(self::CSS_CLASS . '-title');
            $body->classList->addOne(self::CSS_CLASS . '-body');
            $list->appendChild($title, $body);
            if ($active) {
                $list->classList->addOne('active');
            }
            $this->appendChild($list);
            return $this;
        }
    }

}
