<?php

/**
 * Description of Color
 * @author coder
 *
 * Created on: Mar 25, 2025 at 10:23:29 AM
 */

namespace gui\v1\decoration {

    enum Color: int
    {

        case DANGER = 0;
        case SUCCESS = 1;
        case ALERT = 2;
        case INFO = 3;
        case PRIMARY = 4;
        case SECONDARY = 5;
        case TRANSLUSCENT = 6;
    }

}
