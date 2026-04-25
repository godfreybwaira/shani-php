<?php

/**
 * Description of HttpConnection
 * @author goddy
 *
 * Created on: Apr 25, 2026 at 5:51:03 PM
 */

namespace shani\http\enums {

    enum HttpConnection
    {

        case CLOSE;
        case KEEP;
        case AUTO;
    }

}
