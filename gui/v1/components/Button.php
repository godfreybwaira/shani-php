<?php

/**
 * Description of Button
 * @author coder
 *
 * Created on: May 5, 2024 at 8:18:38 PM
 */

namespace gui\v1\components {

    final class Button extends \gui\v1\Component
    {

        private const NAME = 'button';
        private const TYPES = ['', 'type-2', 'type-3'];
        public const TYPE_2 = 1, TYPE_3 = 2;

        public function __construct(string $text = null)
        {
            parent::__construct('button', $text);
            $this->setProps([self::NAME]);
        }

        public function setType(int $type): self
        {
            $this->type = self::TYPES[$type];
            return $this;
        }

        public function setBlock(): self
        {
            $this->setProps([self::NAME . '-block']);
            return $this;
        }
    }

}
