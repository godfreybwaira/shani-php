<?php

/**
 * TargetDevice define the screen size of the view port. All units are in
 * rem or em (1rem = 16px)
 * @author coder
 *
 * Created on: Jul 26, 2024 at 7:00:12 PM
 */

namespace gui\v2 {

    enum TargetDevice: int
    {

        /**
         * Min-width: 0
         */
        case MOBILE = 0;

        /**
         * Min-width: 36rem or 576px
         */
        case TABLET = 36;

        /**
         * Min-width: 48rem or 768px
         */
        case LAPTOP = 48;

        /**
         * Min-width: 62rem or 992px
         */
        case DESKTOP = 62;

        /**
         * Min-width: 75rem or 1200px
         */
        case TV = 75;
    }

}
