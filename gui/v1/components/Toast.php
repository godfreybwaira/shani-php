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

    final class Toast extends Component
    {

        private const NAME = 'toast';

        public function __construct()
        {
            parent::__construct('div');
            $this->addProperty(self::NAME);
        }
    }

}
