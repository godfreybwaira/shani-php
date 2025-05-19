<?php

/**
 * Description of MenuItem
 * @author coder
 *
 * Created on: May 19, 2025 at 11:55:16 AM
 */

namespace gui\v2\components {

    use gui\v2\Component;

    final class MenuItem extends Component
    {

        private ?Component $icon = null;
        private readonly float $fontSize;
        private readonly bool $showLabel;

        private const CSS_CLASSNAME = 'menu-item';

        public function __construct(string $label, float $fontSize = 1.0, bool $showLabel = true)
        {
            parent::__construct('a');
            $this->label = $label;
            $this->fontSize = $fontSize;
            $this->showLabel = $showLabel;
            $this->classList->addOne(self::CSS_CLASSNAME);
            $this->attribute->addOne('title', $label);
        }

        /**
         * Set menu item Icon
         * @param string $alignment Icon alignment either x or y
         * @param string $icons List of CSS classes representing the icon
         * @return self
         */
        public function setIcon(string $alignment, string ...$icons): self
        {
            if ($this->icon === null) {
                $this->icon = new Component('i');
                if ($this->fontSize !== 1.0) {
                    $this->icon->classList->addOne('icon');
                    $this->icon->style->addOne('font-size', (100 * $this->fontSize) . '%');
                }
                $this->classList->addAll(self::CSS_CLASSNAME . '-' . $alignment);
                $this->appendChild($this->icon);
            }
            $this->icon->classList->addAll($icons);
            return $this;
        }

        public function open(): string
        {
            if ($this->showLabel) {
                $label = new Component('span');
                $label->setText($this->label)->classList->addOne('label');
                if ($this->icon !== null) {
                    $label->style->addOne('font-size', (80 * $this->fontSize) . '%');
                }
                $this->appendChild($label);
            }
            $this->attribute->addIfAbsent('href', '#');
            return parent::open();
        }
    }

}
