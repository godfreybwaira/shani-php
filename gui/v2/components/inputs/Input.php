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

        /**
         * Set input mask to hide content inside input box or group
         * @param string $label Hints to the input
         * @return self
         */
        public final function setMask(string $label = null): self
        {
            $this->classList->addOne('input-mask');
            if (strlen($label) > 0) {
                $this->attribute->addOne('data-label', $label);
            }
            return $this;
        }
    }

}
