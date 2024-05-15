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

        private const NAME = 'button', TYPES = ['bold', 'outline'];
        public const TYPE_BOLD = 0, TYPE_OUTLINE = 1;

        private string $type;

        public function __construct(string $text = null)
        {
            parent::__construct('button', $text);
            $this->setType(self::TYPE_BOLD);
            $this->setProps([self::NAME])->setColor(parent::COLOR_PRIMARY);
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

        public function build(): string
        {
            $this->setProps([self::NAME . '-' . $this->type]);
            return parent::build();
        }
    }

}
