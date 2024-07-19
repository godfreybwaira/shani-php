<?php

/**
 * Avatar is a graphical representation of a user or their alter ego or character.
 * @author coder
 *
 * Created on: May 12, 2024 at 9:00:15 AM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class Avatar extends Component
    {

        private const AVATAR = 0;

        /**
         * Set avatar state to on
         */
        public const STATE_ON = 0;

        /**
         * Set avatar state to off
         */
        public const STATE_OFF = 1;

        /**
         * Avatar will stack on top of each other
         */
        public const AVATAR_STACK = 3;
        private const AVATAR_STATES = 4;
        private const PROPS = [
            self::AVATAR => '',
            self::AVATAR_STACK => '',
            self::AVATAR_STATES => [
                self::STATE_ON => '', self::STATE_OFF => ''
            ]
        ];

        public function __construct(?string $content = null)
        {
            parent::__construct('div', self::PROPS);
            $this->setContent($content)->addStyle(self::AVATAR);
        }

        /**
         * Set avatar state
         * @param int $state Avatar state can be one from Avatar::STATE_*
         * @return self
         */
        public function setState(int $state): self
        {
            return $this->addStyle(self::AVATAR_STATES, $state);
        }
    }

}
