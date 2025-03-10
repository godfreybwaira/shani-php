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
    use gui\v1\TargetDevice;

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
        private const TAB = 0, TAB_MENU = 1, TAB_BODY = 2;
        private const MENU_ALIGN = 3;
        private const PROPS = [
            self::TAB => 'grid',
            self::TAB_MENU => 'red',
            self::TAB_BODY => 'blue',
            self::MENU_ALIGN => [
                Style::ALIGN_CENTER => '', Style::ALIGN_END => '',
                Style::ALIGN_START => '', Style::ALIGN_STRETCH => ''
            ]
        ];

        private Component $menu, $body;
        private array $positions = [];

        public function __construct()
        {
            parent::__construct('div', self::PROPS);
            $this->menu = new Component('ul', self::PROPS);
            $this->body = new Component('div', self::PROPS);
            $this->setMenuAlignment(Style::ALIGN_CENTER);
            $this->setMenuPosition(TargetDevice::MOBILE, self::MENU_POS_TOP);
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
         * @param TargetDevice $device Target device
         * @param int $position Menu position
         * @return self
         */
        public function setMenuPosition(TargetDevice $device, int $position): self
        {
            if ($position === self::MENU_POS_TOP) {
                $this->positions[$device->value] = ['rows', 'auto 1fr'];
            } else if ($position === self::MENU_POS_RIGHT) {
                $this->positions[$device->value] = ['columns', '1fr auto'];
            } else if ($position === self::MENU_POS_BOTTOM) {
                $this->positions[$device->value] = ['rows', '1fr auto'];
            } else {
                $this->positions[$device->value] = ['columns', 'auto 1fr'];
            }
            return $this;
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
            $css = null;
            $id = static::createId();
            $mobile = \gui\v1\TargetDevice::MOBILE->value;
            foreach ($this->positions as $device => $query) {
                if ($device === $mobile) {
                    $css .= '#' . $id . '{grid-template-' . $query[0] . ':' . $query[1] . '}';
                } else {
                    $css .= '@media(min-width:' . $device . 'rem){';
                    $css .= '#' . $id . '{grid-template-' . $query[0] . ':' . $query[1] . '}}';
                }
            }
            $style = new Component('style');
            $style->setAttribute('type', 'text/css')->setContent($css);
            $this->appendChildren($this->menu, $this->body, $style)->setAttribute('id', $id);

            return parent::build();
        }
    }

}
