<?php

/**
 * Represent attributes of a component
 * @author coder
 *
 * Created on: Apr 22, 2025 at 9:49:53 AM
 */

namespace gui\v2\props {

    use lib\ds\map\MutableMap;

    final class Attribute extends MutableMap
    {

        #[\Override]
        public function __toString(): string
        {
            $result = '';
            foreach ($this->data as $name => $value) {
                $result .= ' ' . $name . ($value !== null ? '="' . $value . '"' : null);
            }
            return $result;
        }
    }

}
