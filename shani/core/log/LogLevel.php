<?php

/**
 * Description of LogLevel
 * @author coder
 *
 * Created on: Apr 7, 2025 at 3:13:44 PM
 */

namespace shani\core\log {

    enum LogLevel: string
    {

        case WARNING = 'warning';
        case ERROR = 'error';
        case INFO = 'info';
        case EMERGENCY = 'emergency';
    }

}
