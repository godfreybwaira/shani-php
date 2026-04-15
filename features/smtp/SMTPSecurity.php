<?php

/**
 * Description of SMTPSecurity
 * @author goddy
 *
 * Created on: Jul 23, 2025 at 9:23:27 AM
 */

namespace features\smtp {

    enum SMTPSecurity: string
    {

        case TLS = 'tls';
        case SSL = 'ssl';
    }

}
