<?php

/**
 * Modal is a type of window that pops up over the main content of a webpage.
 * It's designed to capture the user's full attention by temporarily disabling
 * interaction with the rest of the page until the user addresses the modal's content.
 * @author coder
 *
 * Created on: May 16, 2024 at 10:09:03 PM
 */

namespace gui\v1\widgets {

    use gui\v1\Component;
    use gui\v1\Style;

    final class Modal extends Component
    {

        /**
         * Display modal as a card
         */
        public const TYPE_CARD = 0;

        /**
         * Position modal at the top and display as a drawer
         */
        public const TYPE_DRAWER_TOP = 1;

        /**
         * Position modal at the bottom and display as a drawer
         */
        public const TYPE_DRAWER_BOTTOM = 2;

        /**
         * Position modal to the left and display as a drawer
         */
        public const TYPE_DRAWER_LEFT = 3;

        /**
         * Position modal to the right and display as a drawer
         */
        public const TYPE_DRAWER_RIGHT = 4;
        ////////////////////
        private const MODAL = 0, MODAL_TYPES = 1, MODAL_WRAPPER = 2;
        private const ANIMATION_BEHAVIOR = 3;
        private const PROPS = [
            self::MODAL => '',
            self::MODAL_TYPES => [
                self::TYPE_CARD => '', self::TYPE_DRAWER_TOP => '',
                self::TYPE_DRAWER_BOTTOM => '', self::TYPE_DRAWER_LEFT => '',
                self::TYPE_DRAWER_RIGHT => ''
            ],
            self::MODAL_WRAPPER => '',
            self::ANIMATION_BEHAVIOR => [
                Style::ANINATION_FADE => '',
                Style::ANINATION_SLIDE_BOTTOM => '',
                Style::ANINATION_SLIDE_LEFT => '',
                Style::ANINATION_SLIDE_RIGHT => '',
                Style::ANINATION_SLIDE_TOP => ''
            ]
        ];

        private bool $wrapped = false;
        private ?Component $navbar = null;
        private Component $wrapper;

        public function __construct()
        {
            parent::__construct('div', self::PROPS);
            $this->addStyle(self::MODAL);
            $this->setType(self::TYPE_CARD);
            $this->wrapper = new Component('div', self::PROPS);
            $this->wrapper->addStyle(self::MODAL_WRAPPER);
        }

        /**
         * Set modal type
         * @param int $modalType Modal type from Modal::TYPE_*
         * @return self
         */
        public function setType(int $modalType): self
        {
            return $this->addStyle(self::MODAL_TYPES, $modalType);
        }

        /**
         * Add modal navigation bar and it's item(s)
         * @param Component $items navigation bar item(s) e.g action buttons
         * @return self
         */
        public function addNavbarItem(Component ...$items): self
        {
            if ($this->navbar === null) {
                $this->navbar = new Component('ul', self::PROPS);
                $this->navbar->addStyle(self::MODAL_NAV);
                $this->wrapper->appendChildren($this->navbar);
            }
            foreach ($items as $item) {
                $list = new Component('li');
                $list->appendChildren($item);
            }
            $this->navbar->appendChildren($list);
            return $this;
        }

        /**
         * Set animation behavior
         * @param int $behavior Animation behavior set using Style::ANIMATION_*
         * @return self
         */
        public function setAnimationBehavior(int $behavior): self
        {
            $this->addStyle(self::ANIMATION_BEHAVIOR, $behavior);
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
