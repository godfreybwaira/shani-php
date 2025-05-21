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
    use gui\v2\decorators\Stripes;

    abstract class ListItem extends Component
    {

        protected function __construct(Size $size, string $tag)
        {
            parent::__construct($tag);
            $this->classList->addAll(['list', $size->value]);
        }

        /**
         * Set list stripes
         * @param Stripes $stripe
         * @return self
         */
        public function setStripes(Stripes $stripe): self
        {
            $this->classList->addOne('list-' . $stripe->value);
        }
    }

}
