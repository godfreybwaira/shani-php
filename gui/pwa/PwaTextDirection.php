<?php

/**
 * Description of PwaTextDirection
 * @author goddy
 *
 * Created on: Apr 8, 2026 at 1:37:06 PM
 */

namespace gui\pwa {

    enum PwaTextDirection: string
    {

        case LTR = 'ltr';
        case RTL = 'rtl';
        case AUTO = 'auto';
    }

}
