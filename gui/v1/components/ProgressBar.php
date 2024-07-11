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

        private const NAME = 'progress-bar';

        public function __construct(float $percent)
        {
            parent::__construct('div');
            $this->addProperty(self::NAME);
            $bar = new Component('span', $percent . '%');
            $bar->setAttribute('style', 'width:' . $percent . '%');
            $this->appendChildren($bar);
        }
    }

}
