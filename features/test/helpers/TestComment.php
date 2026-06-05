<?php

/**
 * Description of TestComment
 * @author goddy
 *
 * @since Jul 18, 2025 at 1:17:25 PM
 */

namespace features\test\helpers {

    enum TestComment
    {

        /**
         * When test has passed
         */
        case PASS;

        /**
         * When test has failed
         */
        case FAIL;
    }

}
