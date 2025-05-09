<?php

/**
 * Description of Class
 * @author coder
 *
 * Created on: Apr 22, 2025 at 9:36:08 AM
 */

namespace gui\v2 {

    use lib\ds\set\MutableSet;

    final class StyleClass extends MutableSet
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
