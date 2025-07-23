<?php

/**
 * Description of SMTPSecurity
 * @author goddy
 *
 * Created on: Jul 23, 2025 at 9:23:27 AM
 */

namespace lib\client {

    enum SMTPSecurity: string
    {

        case TLS = 'tls';
        case SSL = 'ssl';
    }

}
