<?php

/**
 * Avatar is a graphical representation of a user or their alter ego or character.
 * @author coder
 *
 * Created on: May 12, 2024 at 9:00:15 AM
 */

namespace gui\v1\components {

    final class Avatar extends \gui\v1\Component
    {

        private const NAME = 'avatar', STATES = ['off', 'on'];
        public const STATE_ON = 1, STATE_OFF = 0;

        public function __construct(string $content = null)
        {
            parent::__construct('div');
            $this->setContent($content)->addProperty(self::NAME);
        }

        /**
         * Whether avatars to stack on top of each other
         * @return self
         */
        public function setStack(): self
        {
            return $this->addProperty(self::NAME . '-stack');
        }

        /**
         * Set avatar state
         * @param int $state Avatar state can be one from Avatar::STATE_*
         * @return self
         */
        public function setState(int $state): self
        {
            return $this->addProperty(self::NAME . '-state', self::STATES[$state]);
        }
    }

}
