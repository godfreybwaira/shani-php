<?php

/**
 * Description of HorizontalList
 * @author coder
 *
 * Created on: May 21, 2025 at 3:57:12 PM
 */

namespace gui\v2\containers\lists {

    use gui\v2\Component;
    use gui\v2\decorators\Size;

    abstract class ListItem extends Component
    {

        protected function __construct(Size $size, string $tag)
        {
            parent::__construct($tag);
            $this->classList->addAll(['list', $size->value]);
        }
    }

}
