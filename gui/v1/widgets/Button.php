<?php

/**
 * Button element class
 * @author coder
 *
 * Created on: May 5, 2024 at 8:18:38 PM
 */

namespace gui\v1\widgets {

    use gui\v1\Component;
    use gui\v1\Style;

    final class Button extends Component
    {

        private const BUTTON = 0;

        /**
         * Button style 'bold'
         */
        public const APPEARANCE_BOLD = 0;

        /**
         * Button style 'outline'
         */
        public const APPEARANCE_OUTLINE = 1;

        /**
         * Create a 'Block' button
         */
        public const BLOCK = 1;
        private const BUTTON_APPEARANCE = 2;
        private const PROPS = [
            self::BUTTON => 'button',
            self::BLOCK => '',
            self::BUTTON_APPEARANCE => [self::APPEARANCE_BOLD => '', self::APPEARANCE_OUTLINE => ''],
        ];

        public function __construct(string $text = null)
        {
            parent::__construct('button', self::PROPS);
            $this->setContent($text)->addStyle(self::BUTTON);
            $this->setAppearance(self::APPEARANCE_BOLD);
            $this->setColor(Style::COLOR_PRIMARY);
        }

        /**
         * Set a button appearance
         * @param int $ppearance A value set using Button::APPEARANCE_*
         * @return self
         */
        public function setAppearance(int $ppearance): self
        {
            return $this->addStyle(self::BUTTON_APPEARANCE, $ppearance);
        }
    }

}
