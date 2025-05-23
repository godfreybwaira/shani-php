<?php

/**
 * Description of Animation
 * @author coder
 *
 * Created on: Apr 21, 2025 at 3:43:58 PM
 */

namespace gui\v2\decoration {

    enum Animation: string
    {

        case ZOOM_IN = 'animation-zoom-in';
        case SLIDE_LEFT = 'animation-slide-left';
        case SLIDE_RIGHT = 'animation-slide-right';
        case SLIDE_UP = 'animation-slide-up';
        case SLIDE_DOWN = 'animation-slide-down';
    }

}
