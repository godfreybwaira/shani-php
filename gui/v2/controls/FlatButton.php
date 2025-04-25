<?php

/**
 * Description of FlatButton
 * @author coder
 *
 * Created on: Apr 20, 2025 at 7:51:35 PM
 */

namespace gui\v2\controls {

    use gui\v2\Component;

    final class FlatButton extends Component
    {

        public function __construct(string $text)
        {
            parent::__construct('button');
            $this->setText($text);
            $this->classList->addOne('flat-button');
        }
    }

}
