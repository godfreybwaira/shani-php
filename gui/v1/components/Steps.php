<?php

/**
 * Description of Steps
 * @author coder
 *
 * Created on: May 12, 2024 at 12:33:54 PM
 */

namespace gui\v1\components {

    final class Steps extends \gui\v1\Component
    {

        private const NAME = 'steps', TYPES = ['round', 'square'];
        private const SIZES = ['sm', 'md', 'lg', 'xl'];
        public const TYPE_1 = 0, TYPE_2 = 1;

        private string $size = 'md', $type = 'round';

        public function __construct()
        {
            parent::__construct('ul');
            $this->setProps([self::NAME]);
        }

        public function addItem(ListItem $item, bool $current, bool $complete = false): self
        {
            if ($complete) {
                $item->setProps([self::NAME . '-complete']);
            } elseif ($current) {
                $item->setProps([self::NAME . '-current']);
            }
            return $this->appendChildren($item);
        }

        public function setType(int $type): self
        {
            $this->type = self::TYPES[$type];
            return $this;
        }

        public function setAlign(bool $horizontal): self
        {
            if ($horizontal) {
                return $this->setProps([self::NAME . '-h']);
            }
            return $this;
        }

        public function build(): string
        {
            $this->setProps([self::NAME . '-' . $this->type]);
            return parent::build();
        }
    }

}
