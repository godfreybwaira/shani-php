<?php

/**
 * Description of TabPosition
 * @author coder
 *
 * Created on: May 19, 2025 at 10:12:01 AM
 */

namespace gui\v2\decorators {

    enum TabPosition: string
    {

        case TOP = Position::TOP->value;
        case LEFT = Position::LEFT->value;
        case RIGHT = Position::RIGHT->value;
        case BOTTOM = Position::BOTTOM->value;
    }

}
