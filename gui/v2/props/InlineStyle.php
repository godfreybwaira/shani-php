<?php

/**
 * Represent inline style of a component
 * @author coder
 *
 * Created on: Apr 22, 2025 at 9:49:53 AM
 */

namespace gui\v2\props {

    use features\ds\map\WritableMap;

    final class InlineStyle extends WritableMap
    {

        public function asString(): string
        {
            $result = '';
            foreach ($this->data as $name => $value) {
                $result .= $name . ':' . $value . ';';
            }
            return $result;
        }

        #[\Override]
        public function __toString(): string
        {
            return $this->asString();
        }
    }

}
