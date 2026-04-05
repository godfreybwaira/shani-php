<?php

/**
 * Description of SessionStorageChooser
 * @author goddy
 *
 * Created on: Apr 3, 2026 at 6:34:55 PM
 */

namespace shani\persistence\session {

    enum SessionStorageChooser
    {

        /**
         * Handle session using session default mechanism
         */
        case FILE;

        /**
         * Disable session
         */
        case DISABLE;
    }

}
