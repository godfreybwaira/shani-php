<?php

/**
 * Description of PwaIconPurpose
 * @author goddy
 *
 * Created on: Apr 8, 2026 at 1:37:06 PM
 */

namespace gui\pwa {

    enum PwaIconPurpose: string
    {

        case ANY = 'any';
        case MASKABLE = 'maskable';
        case MONOCHROME = 'monochrome';
    }

}
