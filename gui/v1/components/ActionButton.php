<?php

/**
 * ActionButton class for creating more specific custom button(s)
 * @author coder
 *
 * Created on: May 5, 2024 at 8:18:38 PM
 */

namespace gui\v1\components {

    final class ActionButton extends \gui\v1\Component
    {

        private const NAME = 'action-btn';
        private const TYPES = ['prev', 'next', 'times', 'maximize', 'plus'];
        public const TYPE_PREV = 0, TYPE_NEXT = 1, TYPE_TIMES = 2, TYPE_MAXIMIZE = 3, TYPE_PLUS = 4;

        public function __construct(int $buttonType)
        {
            parent::__construct('button');
            $this->addProperty(self::NAME)->setType($buttonType);
        }

        /**
         * Set button type
         * @param int $buttonType Type values from ActionButton::TYPE_*
         * @return self
         */
        public function setType(int $buttonType): self
        {
            return $this->addProperty(self::NAME . '-type', self::TYPES[$buttonType]);
        }
    }

}
