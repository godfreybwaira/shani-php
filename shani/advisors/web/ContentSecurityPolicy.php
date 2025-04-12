<?php

/**
 * Description of ContentSecurityPolicy
 * @author coder
 *
 * Created on: Apr 11, 2025 at 4:43:09 PM
 */

namespace shani\advisors\web {

    enum ContentSecurityPolicy: string
    {

        /**
         * Block Click-jacking attack, upgrade insecure requests, block embedding
         * this application on other domains
         */
        case BASIC = "base-uri 'self';upgrade-insecure-requests;frame-ancestors 'self'";

        /**
         * Disable CSP headers (Not recommended)
         */
        case DISABLE = '';
    }

}
