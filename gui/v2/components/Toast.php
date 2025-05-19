<?php

/**
 * Description of Toast
 * @author coder
 *
 * Created on: May 18, 2025 at 6:24:54 PM
 */

namespace gui\v2\components {

    use gui\v2\Component;
    use gui\v2\decoration\Animation;
    use gui\v2\decoration\Position;

    final class Toast extends Component
    {

        public function __construct(Position $pos = Position::TOP_RIGHT)
        {
            parent::__construct('div');
            $this->classList->addAll(['toast', 'padding-xy', 'width-xs-9', 'width-sm-4', $pos->value]);
            $this->setAnimation(Animation::SLIDE_LEFT);
        }
    }

}
