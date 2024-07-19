<?php

/**
 * ProgressBar  is a visual indicator that shows the progression of a task or
 * process to the user. It's commonly used to display the status of a download,
 * file upload, or any other operation that takes time to complete.
 * @author coder
 *
 * Created on: May 16, 2024 at 3:19:29 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class ProgressBar extends Component
    {

        private const PROGRESS_BAR = 0;
        private const PROPS = [
            self::PROGRESS_BAR => ''
        ];

        public function __construct(float $percent)
        {
            parent::__construct('div', self::PROPS);
            $this->addStyle(self::PROGRESS_BAR);
            $bar = new Component('span');
            $bar->setContent($percent . '%');
            $bar->setAttribute('style', 'width:' . $percent . '%');
            $this->appendChildren($bar);
        }
    }

}
