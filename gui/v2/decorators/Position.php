<?php

/**
 * Description of Position
 * @author coder
 *
 * Created on: May 17, 2025 at 6:42:43 PM
 */

namespace gui\v2\decorators {

    enum Position: string
    {

        case CENTER = 'pos-c';
        case CENTER_LEFT = 'pos-cl';
        case CENTER_RIGHT = 'pos-cr';
        ///////////////////////
        case LEFT = 'pos-l';
        case RIGHT = 'pos-r';
        ///////////////////////
        case BOTTOM = 'pos-b';
        case BOTTOM_LEFT = 'pos-bl';
        case BOTTOM_RIGHT = 'pos-br';
        case BOTTOM_CENTER = 'pos-bc';
        ///////////////////////
        case TOP = 'pos-t';
        case TOP_LEFT = 'pos-tl';
        case TOP_RIGHT = 'pos-tr';
        case TOP_CENTER = 'pos-tc';
    }

}
