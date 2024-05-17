<?php

/**
 * Description of Loader
 * @author coder
 *
 * Created on: May 12, 2024 at 12:33:54 PM
 */

namespace gui\v1\components {

    final class Loader extends \gui\v1\Component
    {

        private const NAME = 'loader', TYPES = ['walk', 'glow'];
        public const TYPE_1 = 0, TYPE_2 = 1;

        private string $type;

        public function __construct()
        {
            parent::__construct('div');
            $this->setType(self::TYPE_1);
            $this->addProps([self::NAME, 'animate']);
        }

        public function setType(int $type): self
        {
            $this->type = self::TYPES[$type];
            return $this;
        }

        public function build(): string
        {
            $this->addProps([self::NAME . '-type-' . $this->type]);
            return parent::build();
        }
    }

}
