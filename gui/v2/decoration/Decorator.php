<?php

/**
 * Description of Decorator
 * @author coder
 *
 * Created on: Apr 20, 2025 at 9:35:43 PM
 */

namespace gui\v2\decoration {

    interface Decorator
    {

        /**
         * Get CSS property in form of <code>property:value;</code>
         * @return string|null
         */
        public function getProperty(): ?string;
    }

}
