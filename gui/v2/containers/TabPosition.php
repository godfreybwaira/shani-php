<?php

/**
 * Description of TabPosition
 * @author coder
 *
 * Created on: May 19, 2025 at 10:12:01 AM
 */

namespace gui\v2\containers {

    enum TabPosition: string
    {

        case TOP = 'tab-top';
        case LEFT = 'tab-left';
        case RIGHT = 'tab-right';
        case BOTTOM = 'tab-bottom';
    }

}
