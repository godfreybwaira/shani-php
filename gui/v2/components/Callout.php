<?php

/**
 * Description of Callout
 * @author coder
 *
 * Created on: May 24, 2025 at 9:33:01 PM
 */

namespace gui\v2\components {

    use gui\v2\Component;
    use gui\v2\decorators\Color;

    final class Callout extends Component
    {

        public function __construct(Color $color)
        {
            parent::__construct('div');
            $this->setInformativeColor($color);
        }
    }

}
