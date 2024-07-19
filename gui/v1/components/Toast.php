<?php

/**
 * Toast is a small, temporary notification that appears on the user's screen to
 * provide feedback or information. It's non-intrusive and usually disappears on
 * its own after a few seconds.
 * @author coder
 *
 * Created on: May 12, 2024 at 11:01:53 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;
    use gui\v1\Style;

    final class Toast extends Component
    {

        private const TOAST = 0, TOAST_POS = 1;
        private const PROPS = [
            self::TOAST => '',
            self::TOAST_POS => [
                Style::POS_BC => '', Style::POS_BL => '',
                Style::POS_BR => '', Style::POS_CL => '',
                Style::POS_CR => '', Style::POS_TC => '',
                Style::POS_TL => '', Style::POS_TR => ''
            ]
        ];

        public function __construct()
        {
            parent::__construct('div', self::PROPS);
            $this->addStyle(self::TOAST)->setPosition(null);
        }

        public function setPosition(?int $position): self
        {
            return $this->addStyle(self::TOAST_POS, $position ?? Style::POS_TC);
        }
    }

}
