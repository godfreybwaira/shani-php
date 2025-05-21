<?php

/**
 * Description of HorizontalList
 * @author coder
 *
 * Created on: May 21, 2025 at 3:57:12 PM
 */

namespace gui\v2\containers\lists {

    use gui\v2\decorators\Direction;
    use gui\v2\decorators\Size;

    final class VerticalList extends ListItem
    {

        /**
         * Create a responsive list
         * @param Size $size List size (font size and padding)
         * @param string $tag Default HTML tag to be used for list creation
         */
        public function __construct(Size $size = Size::MEDIUM, string $tag = 'ul')
        {
            parent::__construct($size, $tag);
            $this->classList->addOne('list-' . Direction::VERTICAL->value);
        }
    }

}
