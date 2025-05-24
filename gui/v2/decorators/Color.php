<?php

/**
 * Informative colors
 * @author coder
 *
 * Created on: May 24, 2025 at 4:33:02 PM
 */

namespace gui\v2\decorators {

    enum Color: string
    {

        case DANGER = 'color-danger';
        case INFO = 'color-info';
        case SUCCESS = 'color-success';
        case ALERT = 'color-alert';
        case DISABLE = 'color-disable';
    }

}
