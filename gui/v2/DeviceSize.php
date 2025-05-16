<?php

/**
 * DeviceSize define the screen size of the view port.
 * @author coder
 *
 * Created on: Jul 26, 2024 at 7:00:12 PM
 */

namespace gui\v2 {

    enum DeviceSize: string
    {

        case MOBILE = 'xs';
        case TABLET = 'sm';
        case LAPTOP = 'md';
        case DESKTOP = 'lg';
        case TV = 'xl';
    }

}
