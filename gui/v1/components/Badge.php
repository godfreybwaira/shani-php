<?php

/**
 * Badge is a small graphical element that represents a piece of information, often
 * used to highlight the status of an item or to draw attention to a particular
 * element on a webpage.
 * @author coder
 *
 * Created on: May 12, 2024 at 10:17:47 AM
 */

namespace gui\v1\components {

    use gui\v1\Component;
    use gui\v1\Style;

    final class Badge extends Component
    {

        private const BADGE = 0;
        private const PROPS = [
            self::BADGE => ''
        ];

        public function __construct(?string $text = null)
        {
            parent::__construct('span', self::PROPS);
            $this->setContent($text)->addStyle(self::BADGE);
            if (Style::SIZE_DEFAULT === Style::SIZE_SM) {
                $this->setSpacing(Style::SIZE_SM);
            } else {
                $this->setSpacing(Style::SIZE_DEFAULT - 1);
            }
        }
    }

}
