<?php

/**
 * ActionButton class for creating more specific custom button(s)
 * @author coder
 *
 * Created on: May 5, 2024 at 8:18:38 PM
 */

namespace gui\v1\widgets {

    use gui\v1\Component;

    final class ActionButton extends Component
    {

        private const ACTION_BUTTON = 0;

        /**
         * 'Previous' action button
         */
        public const TYPE_PREV = 1;

        /**
         * 'Next' action button
         */
        public const TYPE_NEXT = 2;

        /**
         * 'Times' action button
         */
        public const TYPE_TIMES = 3;

        /**
         * 'Maximize' action button
         */
        public const TYPE_MAXIMIZE = 4;

        /**
         * 'Plus' action button
         */
        public const TYPE_PLUS = 5, BUTTON_TYPES = 6;
        private const PROPS = [
            self::BUTTON_TYPES => [
                self::TYPE_MAXIMIZE => '', self::TYPE_NEXT => '', self::TYPE_PLUS => '',
                self::TYPE_TIMES => '', self::TYPE_PREV => ''
            ],
            self::ACTION_BUTTON => '',
        ];

        /**
         * Create an action button
         * @param int $buttonType Type values from ActionButton::TYPE_*
         */
        public function __construct(int $buttonType)
        {
            parent::__construct('button', self::PROPS);
            $this->addStyle(self::ACTION_BUTTON);
            $this->setType($buttonType);
        }

        /**
         * Set button type
         * @param int $buttonType Type values from ActionButton::TYPE_*
         * @return self
         */
        public function setType(int $buttonType): self
        {
            return $this->addStyle(self::BUTTON_TYPES, $buttonType);
        }
    }

}
