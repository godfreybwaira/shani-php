<?php

/**
 * Description of PwaOrientation
 * @author goddy
 *
 * Created on: Apr 8, 2026 at 1:37:06 PM
 */

namespace gui\pwa\enums {


    enum PwaOrientation: string
    {

        case ANY = 'any';
        case NATURAL = 'natural';
        case PORTRAIT = 'portrait';
        case PORTRAIT_PRIMARY = 'portrait-primary';
        case PORTRAIT_SECONDARY = 'portrait-secondary';
        case LANDSCAPE = 'landscape';
        case LANDSCAPE_PRIMARY = 'landscape-primary';
        case LANDSCAPE_SECONDARY = 'landscape-secondary';
    }

}
