<?php

/**
 * Description of StaticAssetServers
 * @author goddy
 *
 * Created on: Apr 19, 2026 at 10:07:04 PM
 */

namespace shani\assets {

    enum StaticAssetServers
    {

        /**
         * Select this framework as default static asset server
         */
        case SHANI;

        /**
         * Select Apache as default static asset server. You must configure Apache
         * to serve static asset. For public assets use /0/ as an alias. For other
         * assets see <code>Configuration::appPublicStorage</code> and <code>Configuration::appProtectedStorage</code>
         */
        case APACHE;

        /**
         * Select Nginx as default static asset server. You must configure Nginx
         * to serve static asset. For public assets use /0/ as an alias. For other
         * assets see <code>Configuration::appPublicStorage</code> and <code>Configuration::appProtectedStorage</code>
         */
        case NGINX;

        /**
         * Disable serving static assets.
         */
        case DISABLE;
    }

}
