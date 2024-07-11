<?php

/**
 * Button element class
 * @author coder
 *
 * Created on: May 5, 2024 at 8:18:38 PM
 */

namespace gui\v1\components {

    final class Button extends \gui\v1\Component
    {

        private const NAME = 'button', TYPES = ['bold', 'outline'];
        public const TYPE_BOLD = 0, TYPE_OUTLINE = 1;

        public function __construct(string $text = null)
        {
            parent::__construct('button');
            $this->setContent($text)->setType(self::TYPE_BOLD);
            $this->addProperty(self::NAME)->setColor(parent::COLOR_PRIMARY);
        }

        /**
         * Set button type
         * @param int $buttonType Button type from Button::TYPE_*
         * @return self
         */
        public function setType(int $buttonType): self
        {
            return $this->addProperty(self::NAME . '-type', self::TYPES[$buttonType]);
        }

        /**
         * Set a button to behave like block element
         * @return self
         */
        public function setBlock(): self
        {
            $this->addProperty(self::NAME . '-block');
            return $this;
        }
    }

}
