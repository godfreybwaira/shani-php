<?php

/**
 * Description of LoggingLevel
 * @author coder
 *
 * Created on: Apr 7, 2025 at 3:13:44 PM
 */

namespace features\logging {

    enum LoggingLevel
    {

        case WARNING;
        case ERROR;
        case INFO;
        case EMERGENCY;
    }

}
