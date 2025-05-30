<?php

/**
 * Description of MenuItem
 * @author coder
 *
 * Created on: May 19, 2025 at 11:55:16 AM
 */

namespace gui\v2\components {

    use gui\v2\Component;
    use gui\v2\decorators\Direction;

    final class MenuItem extends Component
    {

        private readonly Component $label, $icon;

        /**
         * Create clickable menu item
         * @param string $hint Menu hint text. Also is used as a menu label
         * @param Direction $direction The way items on a menu item flows
         */
        public function __construct(string $hint, Direction $direction = Direction::HORIZONTAL)
        {
            parent::__construct('a');
            $this->icon = new Component('i');
            $this->label = new Component('span');
            $this->appendChild($this->icon, $this->label);
            $this->classList->addAll(['menu-item', $direction->value]);
            $this->label->setText($hint)->classList->addOne('menu-label');
            $this->attribute->addAll(['title' => $hint, 'href' => 'javascript:;']);
        }

        /**
         * Set menu item Icon
         * @param string $icons List of CSS classes representing the icon
         * @return self
         */
        public function setIcon(string ...$icons): self
        {
            $this->icon->classList->addAll($icons);
            return $this;
        }

        /**
         * Get menu item icon
         * @return Component
         */
        public function getIcon(): Component
        {
            return $this->icon;
        }

        /**
         * Get menu item label
         * @return Component
         */
        public function getLabel(): Component
        {
            return $this->label;
        }
    }

}
