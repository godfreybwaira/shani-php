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
    use gui\v2\decorators\Size;

    final class MenuItem extends Component
    {

        private ?Component $icon = null;
        private readonly bool $showLabel;

        private const CSS_CLASSNAME = 'menu-item';

        /**
         * Create clickable menu item
         * @param string $label Menu text
         * @param Direction $direction The way items on a menu item flows
         * @param bool $showLabel Whether to show label or not
         */
        public function __construct(string $label, Direction $direction, bool $showLabel = true)
        {
            parent::__construct('a');
            $this->label = $label;
            $this->showLabel = $showLabel;
            $this->classList->addAll([self::CSS_CLASSNAME, $direction->value]);
            $this->attribute->addOne('title', $label);
        }

        /**
         * Set menu item Icon
         * @param Size $size Icon size
         * @param string $icons List of CSS classes representing the icon
         * @return self
         */
        public function setIcon(Size $size, string ...$icons): self
        {
            if ($this->icon === null) {
                $this->icon = new Component('i');
                $this->icon->classList->addOne($size->value);
                $this->appendChild($this->icon);
            }
            $this->icon->classList->addAll($icons);
            return $this;
        }

        public function open(): string
        {
            if ($this->showLabel) {
                $label = new Component('span');
                $label->setText($this->label);
                if ($this->icon !== null) {
                    $label->style->addOne('font-sm');
                }
                $this->appendChild($label);
            }
            $this->attribute->addIfAbsent('href', '#');
            return parent::open();
        }
    }

}
