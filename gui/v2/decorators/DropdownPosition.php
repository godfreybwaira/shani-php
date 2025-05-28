<?php

/**
 * Description of DropdownPosition
 * @author coder
 *
 * Created on: May 28, 2025 at 12:40:46 PM
 */

namespace gui\v2\decorators {

    enum DropdownPosition: string
    {

        case TOP_LEFT = Position::TOP_LEFT->value;
        case TOP_RIGHT = Position::TOP_RIGHT->value;
        case BOTTOM_RIGHT = Position::BOTTOM_RIGHT->value;
        case BOTTOM_LEFT = Position::BOTTOM_LEFT->value;
    }

}
