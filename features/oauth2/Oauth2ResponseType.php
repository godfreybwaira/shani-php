<?php

/**
 * Description of Oauth2ResponseType
 * @author goddy
 *
 * Created on: Mar 18, 2026 at 9:20:01 AM
 */

namespace features\oauth2 {

    enum Oauth2ResponseType
    {

        /**
         * OAuth response return error
         */
        case ERROR;

        /**
         * Oauth response succeed
         */
        case OK;
    }

}
