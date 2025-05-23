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

        /**
         * Create Accordion
         * @param bool $collapse If true, then all the items are collapsed except
         * the one with class active.
         */
        protected function __construct(bool $collapse = true)
        {
            parent::__construct('ul');
            $this->classList->addAll(['accordion', 'borders']);
            if ($collapse) {
                $this->classList->addOne('accordion-collapse');
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
            $list->appendChild($title, $body);
            if ($active) {
                $list->classList->addOne('active');
            }
            $this->appendChild($list);
            return $this;
        }
    }

}
