<?php

/**
 * Description of Position
 * @author coder
 *
 * Created on: May 17, 2025 at 6:42:43 PM
 */

namespace gui\v2\decoration {

    enum Position: string
    {

        case CENTER = 'pos-center';
        case CENTER_LEFT = 'pos-center-left';
        case CENTER_RIGHT = 'pos-center-right';
        ///////////////////////
        case LEFT = 'pos-left';
        case RIGHT = 'pos-right';
        ///////////////////////
        case BOTTOM = 'pos-bottom';
        case BOTTOM_LEFT = 'pos-bottom-left';
        case BOTTOM_RIGHT = 'pos-bottom-right';
        case BOTTOM_CENTER = 'pos-bottom-center';
        ///////////////////////
        case TOP = 'pos-top';
        case TOP_LEFT = 'pos-top-left';
        case TOP_RIGHT = 'pos-top-right';
        case TOP_CENTER = 'pos-top-center';
    }

}
