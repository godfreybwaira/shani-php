<?php

/**
 * Description of SMTPSecurityType
 * @author goddy
 *
 * Created on: Jul 23, 2025 at 9:23:27 AM
 */

namespace features\smtp {

    enum SMTPSecurityType
    {

        case TLS;
        case SSL;
        case NONE;
    }

}
