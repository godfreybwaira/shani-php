<?php

/**
 * Description of ProgressBar
 * @author coder
 *
 * Created on: May 16, 2024 at 3:19:29 PM
 */

namespace gui\v1\components {

    use gui\v1\Component;

    final class ProgressBar extends Component
    {

        private const NAME = 'progress-bar';

        public function __construct(int $percent)
        {
            parent::__construct('div');
            $this->addProperty(self::NAME);
            $bar = new Component('span', $percent . '%');
            $bar->setAttribute('style', 'width:' . $percent . '%');
            $this->appendChildren($bar);
        }
    }

}
