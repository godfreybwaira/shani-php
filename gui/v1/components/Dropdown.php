<?php

/**
 * Dropdown is a user interface element that allows users to select one option
 * from a list of options that "drops down" when the element is interacted with.
 * @author coder
 *
 * Created on: May 11, 2024 at 8:05:45 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class Dropdown extends Component
    {

        private ?Component $body, $header;

        private const NAME = 'dropdown';

        public function __construct()
        {
            parent::__construct('ul');
            $this->addProperty(self::NAME);
            $this->header = $this->body = null;
        }

        /**
         * Set dropdown header
         * @param Component $header Dropdown header
         * @return self
         */
        public function setHeader(Component $header): self
        {
            $this->header = $header;
            return $this;
        }

        /**
         * Set dropdown content
         * @param Component $body Dropdown Content
         * @return self
         */
        public function setBody(Component $body): self
        {
            $this->body = $body;
            return $this;
        }

        public function build(): string
        {
            $title = new Component('li', false);
            $body = new Component('li', false);
            $title->appendChildren($this->header);
            $body->appendChildren($this->body);
            $this->appendChildren($title, $body);
            return parent::build();
        }
    }

}
