<?php

/**
 * Description of PwaDisplayOverride
 * @author goddy
 *
 * Created on: Apr 8, 2026 at 1:37:06 PM
 */

namespace features\pwa\enums {

    enum PwaDisplayOverride: string
    {

        case WINDOW_CONTROLS_OVERLAY = 'window-controls-overlay';
        case TABBED = 'tabbed';
        case MINIMAL_UI = 'minimal-ui';
        case STANDALONE = 'standalone';
    }

}
