<?php

/**
 * Modal is a type of window that pops up over the main content of a webpage.
 * It's designed to capture the user's full attention by temporarily disabling
 * interaction with the rest of the page until the user addresses the modal's content.
 * @author coder
 *
 * Created on: May 16, 2024 at 10:09:03 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class Modal extends Component
    {

        private const MODAL = 0, MODAL_TYPES = 1, MODAL_WRAPPER = 2;
        public const TYPE_DRAWER = 1, TYPE_CARD = 0;
        private const PROPS = [
            self::MODAL => '',
            self::MODAL_TYPES => [
                self::TYPE_CARD => '', self::TYPE_DRAWER => ''
            ],
            self::MODAL_WRAPPER => ''
        ];

        private bool $wrapped = false;
        private ?Component $navbar = null;
        private Component $wrapper;

        public function __construct()
        {
            parent::__construct('div', self::PROPS);
            $this->addProperty(self::MODAL);
            $this->setType(self::TYPE_CARD);
            $this->wrapper = new Component('div', self::PROPS);
            $this->wrapper->addProperty(self::MODAL_WRAPPER);
        }

        /**
         * Set modal type
         * @param int $modalType Modal type from Modal::TYPE_*
         * @return self
         */
        public function setType(int $modalType): self
        {
            return $this->addProperty(self::MODAL_TYPES, $modalType);
        }

        /**
         * Add modal navigation bar and it's item(s)
         * @param Component $items navigation bar item(s)
         * @return self
         */
        public function addNavbarItem(Component ...$items): self
        {
            if ($this->navbar === null) {
                $this->navbar = new Component('ul', self::PROPS);
                $this->navbar->addProperty(self::MODAL_NAV);
                $this->wrapper->appendChildren($this->navbar);
            }
            foreach ($items as $item) {
                $list = new Component('li');
                $list->appendChildren($item);
            }
            $this->navbar->appendChildren($list);
            return $this;
        }

        public function build(): string
        {
            if (!$this->wrapped) {
                $this->wrapped = true;
                return $this->wrapper->appendChildren($this)->build();
            }
            return parent::build();
        }
    }

}
