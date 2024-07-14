<?php

/**
 * Button element class
 * @author coder
 *
 * Created on: May 5, 2024 at 8:18:38 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;
    use gui\v1\Style;

    final class Button extends Component
    {

        private const BUTTON = 0;

        /**
         * Button type 'bold'
         */
        public const TYPE_BOLD = 0;

        /**
         * Button type 'outline'
         */
        public const TYPE_OUTLINE = 1;

        /**
         * Create 'Block' button
         */
        public const BLOCK = 1;
        private const BUTTON_TYPES = 2;
        private const PROPS = [
            self::BUTTON => '',
            self::BLOCK => '',
            self::BUTTON_TYPES => [self::TYPE_BOLD => '', self::TYPE_OUTLINE => ''],
        ];

        public function __construct(string $text = null)
        {
            parent::__construct('button', self::PROPS);
            $this->setContent($text)->addProperty(self::BUTTON);
            $this->setType(self::TYPE_BOLD);
            $this->setColor(Style::COLOR_PRIMARY);
        }

        /**
         * Set button type
         * @param int $buttonType Type values from Button::TYPE_*
         * @return self
         */
        public function setType(int $buttonType): self
        {
            return $this->addProperty(self::BUTTON_TYPES, $buttonType);
        }
    }

}
