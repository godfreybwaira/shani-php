<?php

/**
 * Description of Divider
 * @author coder
 *
 * Created on: May 12, 2024 at 11:42:47 AM
 */

namespace gui\v1\components {

    final class Divider
    {

        private const NAME = 'divider';

        public function __construct(string $text = null)
        {
            parent::__construct('div', $text);
            $this->setProps([self::NAME]);
        }

        public function setAlign(bool $vertical): self
        {
            if ($vertical) {
                return $this->setProps([self::NAME . '-align-v']);
            }
            return $this;
        }
    }

}
