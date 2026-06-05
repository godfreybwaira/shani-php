<?php

/**
 * Description of PwaDisplayMode
 * @author goddy
 *
 * @since Apr 8, 2026 at 1:37:06 PM
 */

namespace features\pwa\enums {

    enum PwaDisplayMode: string
    {

        case FULLSCREEN = 'fullscreen';
        case STANDALONE = 'standalone';
        case MINIMAL_UI = 'minimal-ui';
        case BROWSER = 'browser';
    }

}
