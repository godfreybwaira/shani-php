<?php

/**
 * Description of PwaFormFactor
 * @author goddy
 *
 * Created on: Apr 8, 2026 at 1:47:10 PM
 */

namespace features\pwa\enums {

    enum PwaFormFactor: string
    {

        /**
         * Typically for Desktop/Tablet screenshots
         */
        case WIDE = 'wide';

        /**
         * Typically for Mobile screenshots
         */
        case NARROW = 'narrow';
    }

}
