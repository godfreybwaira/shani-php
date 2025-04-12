<?php

/**
 * Description of RespourceAccessPolicy
 * @author coder
 *
 * Created on: Mar 25, 2025 at 10:09:50 AM
 */

namespace shani\advisors\web {

    enum RespourceAccessPolicy: string
    {

        /**
         *  allows resource access on this application from this domain only
         */
        case THIS_DOMAIN = 'same-origin';

        /**
         *  allows resource access on this application from this domain and it's subdomain
         */
        case THIS_DOMAIN_AND_SUBDOMAIN = 'same-site';

        /**
         *  allows resource access on this application from any domain (Not recommended)
         */
        case ANY_DOMAIN = 'cross-origin';

        /**
         *  Do not use resource access policy (Not recommended)
         */
        case DISABLED = '';
    }

}