<?php

/**
 * Description of PwaAppPlatform
 * @author goddy
 *
 * Created on: Apr 9, 2026 at 12:25:50 PM
 */

namespace gui\pwa\enums {

    enum PwaAppPlatform: string
    {

        case PLAY = 'play';
        case ITUNES = 'itunes';
        case WINDOWS = 'windows';
        case WEBAPP = 'webapp';
        case FDROID = 'f-droid';
        case AMAZON = 'amazon';
    }

}
