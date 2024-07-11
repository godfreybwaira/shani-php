<?php

/**
 * Callout is a design element used to draw attention to or highlight important
 * information on a webpage. It can be a box, banner, or any visual cue that stands
 * out from the rest of the content to grab the user's attention.
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
            $this->setSpacing(parent::SIZE_DEFAULT)->setColor($color);
        }
    }

}
