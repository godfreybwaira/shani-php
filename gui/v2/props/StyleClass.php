<?php

/**
 * Description of Class
 * @author coder
 *
 * Created on: Apr 22, 2025 at 9:36:08 AM
 */

namespace gui\v2\props {

    use features\ds\set\WritableSet;

    final class StyleClass extends WritableSet
    {

        /**
         * Convert style classes to string separated by a single space
         * @return string
         */
        public function asString(): string
        {
            return implode(' ', $this->toArray());
        }

        #[\Override]
        public function __toString(): string
        {
            return $this->asString();
        }
    }

}
