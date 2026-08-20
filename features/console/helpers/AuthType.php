<?php

/**
 * Description of AuthType
 * @author goddy
 *
 * @since v1.0: Aug 18, 2026 at 1:41:20 PM
 */

namespace features\console\helpers {

    enum AuthType: string
    {

        case AUTH_BASIC = 'basic';
        case AUTH_JWT = 'jwt';
        case AUTH_DEFAULT = 'default';
    }

}
