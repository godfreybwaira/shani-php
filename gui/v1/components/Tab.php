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
    use gui\v1\Style;

    final class Tab extends Component
    {

        /**
         * Place a tab menu at the top
         */
        public const MENU_POS_TOP = 0;

        /**
         * Place a tab menu at the bottom
         */
        public const MENU_POS_BOTTOM = 1;

        /**
         * Place a tab menu on left
         */
        public const MENU_POS_LEFT = 2;

        /**
         * Place a tab menu on right
         */
        public const MENU_POS_RIGHT = 3;
        //////////////////
        private const TAB = 0, MENU_POS = 1, TAB_MENU = 2, TAB_BODY = 3;
        private const MENU_ALIGN = 4;
        private const PROPS = [
            self::TAB => '',
            self::TAB_MENU => '',
            self::TAB_BODY => '',
            self::MENU_ALIGN => [
                Style::ALIGN_CENTER => '', Style::ALIGN_END => '',
                Style::ALIGN_START => '', Style::ALIGN_STRETCH => ''
            ],
            self::MENU_POS => [
                self::MENU_POS_BOTTOM => '', self::MENU_POS_LEFT => '',
                self::MENU_POS_RIGHT => '', self::MENU_POS_TOP => ''
            ]
        ];

        private Component $menu, $body;
        private int $position;

        public function __construct()
        {
            parent::__construct('div', self::PROPS);
            $this->position = self::MENU_POS_TOP;
            $this->menu = new Component('ul', self::PROPS);
            $this->body = new Component('div', self::PROPS);
            $this->setMenuAlignment(Style::ALIGN_CENTER);
            $this->setMenuPosition($this->position);
            $this->menu->addStyle(self::TAB_MENU);
            $this->body->addStyle(self::TAB_BODY);
            $this->addStyle(self::TAB);
        }

        /**
         * Create a tab menu item. The menu item will be wrapped in list (li) element
         * @param Component $menuItem menu button
         * @param bool $active If set to true, then it's content will be shown by default.
         * @return self
         */
        public function addMenuItem(Component $menuItem, bool $active = false): self
        {
            $list = new Component('li');
            $list->setActive($active)->appendChildren($menuItem);
            $this->menu->appendChildren($list);
            return $this;
        }

        /**
         * Position a tab menu according to position specified using Tab::MENU_POS_*
         * @param int $position Menu position
         * @return self
         */
        public function setMenuPosition(int $position): self
        {
            $this->position = $position;
            return $this->addStyle(self::MENU_POS, $position);
        }

        /**
         * Align menu items according to alignment specified using Style::ALIGN_*
         * @param int $alignment Alignment
         * @return self
         */
        public function setMenuAlignment(int $alignment): self
        {
            $this->menu->addStyle(self::MENU_ALIGN, $alignment);
            return $this;
        }

        public function build(): string
        {
            if ($this->position === self::MENU_POS_TOP || $this->position === self::MENU_POS_LEFT) {
                $this->appendChildren($this->menu, $this->body);
            } else {
                $this->appendChildren($this->body, $this->menu);
            }
            return parent::build();
        }
    }

}
