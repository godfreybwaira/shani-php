<?php

/**
 * Description of OpenBehavior
 * @author coder
 *
 * Created on: Mar 25, 2025 at 12:00:54 PM
 */

namespace gui\v1\decoration {

    enum OpenBehavior: string
    {

        /**
         * Open the current element and close all other. This is the default behavior
         */
        case CLOSE_OTHER = '';

        /**
         * Open the current element without considering the state of other element.
         */
        case OPEN_ALL = '';

        /**
         * This will disable the opening behavior. All elements are open by default.
         */
        case NONE = '';
    }

}
