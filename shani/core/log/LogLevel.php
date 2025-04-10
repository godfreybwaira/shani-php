<?php

/**
 * Description of LogLevel
 * @author coder
 *
 * Created on: Apr 7, 2025 at 3:13:44 PM
 */

namespace shani\core\log {

    enum LogLevel
    {

        case WARNING;
        case ERROR;
        case INFO;
        case EMERGENCY;
    }

}
