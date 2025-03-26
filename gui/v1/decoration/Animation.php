<?php

/**
 * Description of Animation
 * @author coder
 *
 * Created on: Mar 25, 2025 at 10:21:16 AM
 */

namespace gui\v1\decoration {

    enum Animation: string
    {

        case SLIDE_LEFT = 0;
        case SLIDE_RIGHT = 1;
        case SLIDE_TOP = 2;
        case SLIDE_BOTTOM = 3;
        case FADE = 4;
    }

}
