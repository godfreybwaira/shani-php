<?php

/**
 * Description of AnimationType
 * @author coder
 *
 * Created on: Apr 21, 2025 at 3:43:58 PM
 */

namespace gui\v2\decoration {

    enum AnimationType: string
    {

        case SLIDE_UP = 'translateY(100%)';
        case SLIDE_DOWN = 'translateY(-100%)';
        case SLIDE_LEFT = 'translateX(100%)';
        case SLIDE_RIGHT = 'translateX(-100%)';
        case RISE_UP = 'rotate(90deg)';
        case GROW = 'rotateX(90deg)';
        case SHRINK = 'rotateX(90deg)';
        case ZOOM_IN = 'scale(1.2)';
        case ZOOM_OUT = 'scale(1.2)';
    }

}
