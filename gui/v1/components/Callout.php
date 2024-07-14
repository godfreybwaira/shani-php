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

    use gui\v1\Component;
    use gui\v1\Style;

    final class Callout extends Component
    {

        private const CALLOUT = 0;
        private const PROPS = [
            self::CALLOUT => '',
        ];

        public function __construct(int $color, string $text = null)
        {
            parent::__construct('div', self::PROPS);
            $this->setContent($text)->addProperty(self::CALLOUT);
            $this->setSpacing(Style::SIZE_DEFAULT);
            $this->setColor($color);
        }
    }

}
