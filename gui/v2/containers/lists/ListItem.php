<?php

/**
 * Description of ListItem
 * @author coder
 *
 * Created on: May 21, 2025 at 3:57:12 PM
 */

namespace gui\v2\containers\lists {

    use gui\v2\Component;
    use gui\v2\decorators\Direction;
    use gui\v2\decorators\Size;

    final class ListItem extends Component
    {

        /**
         * Create a responsive list
         * @param Direction $dir List direction
         * @param Size $size List size (font size and padding)
         */
        public function __construct(Direction $dir, Size $size = Size::MEDIUM)
        {
            parent::__construct('ul');
            $this->classList->addAll(['list', $size->value, $dir->value]);
        }
    }

}
