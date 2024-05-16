<?php

/**
 * Description of Avatar
 * @author coder
 *
 * Created on: May 12, 2024 at 9:00:15 AM
 */

namespace gui\v1\components {

    final class Avatar extends \gui\v1\Component
    {

        private const NAME = 'avatar', STATES = ['off', 'on'];
        public const STATE_ON = 1, STATE_OFF = 0;

        private ?string $state = null;

        public function __construct(string $content = null)
        {
            parent::__construct('div', $content);
            $this->setProps([self::NAME]);
        }

        public function setStack(): self
        {
            return $this->setProps([self::NAME . '-stack']);
        }

        public function setState(int $state): self
        {
            $this->state = self::STATES[$state];
            return $this;
        }

        public function build(): string
        {
            if ($this->state !== null) {
                $this->setProps([self::NAME . '-' . $this->state]);
            }
            return parent::build();
        }
    }

}
