<?php

/**
 * Description of Shadow
 * @author coder
 *
 * Created on: Mar 25, 2025 at 11:22:37 AM
 */

namespace gui\v1\decoration {

    enum Shadow: int
    {

        case XY = 0;
        case TOP_RIGHT = 1;
        case TOP_LEFT = 2;
        case BOTTOM_LEFT = 3;
        case BOTTOM_RIGHT = 4;
        case DEFAULT_SHADOW = self::BOTTOM_RIGHT;
    }

}

