<?php

/**
 * TargetDevice interface define the screen size of the view port. All units are in
 * rem or em (1rem = 16px)
 * @author coder
 *
 * Created on: Jul 26, 2024 at 7:00:12 PM
 */

namespace gui\v1 {

    interface TargetDevice
    {

        /**
         * Min-width: 0
         */
        public const MOBILE = 0;

        /**
         * Min-width: 36rem or 576px
         */
        public const TABLET = 36;

        /**
         * Min-width: 48rem or 768px
         */
        public const LAPTOP = 48;

        /**
         * Min-width: 62rem or 992px
         */
        public const DESKTOP = 62;

        /**
         * Min-width: 75rem or 1200px
         */
        public const TV = 75;
    }

}
