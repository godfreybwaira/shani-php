<?php

/**
 * DeviceSize define the screen size of the view port.
 * @author coder
 *
 * Created on: Jul 26, 2024 at 7:00:12 PM
 */

namespace gui\v2\props {

    enum DeviceSize: string
    {

        case MOBILE = 'sm';
        case LAPTOP = 'md';
        case DESKTOP = 'lg';

//        case TABLET = 'sm';
//        case TV = 'xl';
    }

}
