<?php

/**
 * Tab is a user interface component that allows users to switch between multiple
 * documents, views, or data sets within the same context and space on a webpage.
 * @author coder
 *
 * Created on: May 12, 2024 at 12:33:54 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class Tab extends Component
    {

        private bool $wrapped = false;

        private const TAB = 0;
        private const PROPS = [
            self::TAB => ''
        ];

        public function __construct()
        {
            parent::__construct('ul', self::PROPS);
            $this->addProperty(self::TAB);
        }

        /**
         * Add a link button to a tab
         * @param Component $link Link button
         * @param bool $active If set to true, then it's content will be shown by
         * default.
         * @return self
         */
        public function addLink(Component $link, bool $active = false): self
        {
            $list = new Component('li');
            $list->setActive($active)->appendChildren($link);
            $this->appendChildren($list);
        }

        public function build(): string
        {
            if (!$this->wrapped) {
                $this->wrapped = true;
                $wrapper = new Component('div');
                $body = new Component('div');
                return $wrapper->appendChildren($this, $body)->build();
            }
            return parent::build();
        }
    }

}
