<?php

/**
 * Description of Callout
 * @author coder
 *
 * Created on: May 12, 2024 at 9:45:13 AM
 */

namespace gui\v1\components {

    final class Callout extends \gui\v1\Component
    {

        private const NAME = 'callout';

        public function __construct(int $color, string $text = null)
        {
            parent::__construct('div');
            $this->setContent($text)->addProperty(self::NAME);
            $this->setGutters(parent::SIZE_DEFAULT)->setColor($color);
        }
    }

}
