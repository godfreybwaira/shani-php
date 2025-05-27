<?php

/**
 * Description of Input
 * @author coder
 *
 * Created on: May 27, 2025 at 4:34:11 PM
 */

namespace gui\v2\components\inputs {

    use gui\v2\Component;

    abstract class Input extends Component
    {

        public final function setMask(): self
        {
            $this->classList->addOne('input-mask');
            return $this;
        }
    }

}
