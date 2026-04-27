<?php

/**
 * Description of StaticAssetServers
 * @author goddy
 *
 * Created on: Apr 19, 2026 at 10:07:04 PM
 */

namespace features\assets {

    /**
     * Enum representing supported static asset servers for the application.
     *
     * This enum allows developers to select which server or framework
     * should handle static asset delivery (e.g., images, CSS, JavaScript).
     *
     * Options:
     * - SHANI → Use this framework as the default static asset server.
     * - APACHE → Use Apache as the static asset server (requires configuration).
     * - NGINX → Use Nginx as the static asset server (requires configuration).
     * - DISABLE → Disable serving static assets entirely.
     *
     * Notes:
     * - For Apache and Nginx, you must configure the server to serve static assets.
     * - Public assets should use `/0/` as an alias when configured with Apache or Nginx.
     * - DISABLE is useful when static assets are served externally (e.g., CDN).
     */
    enum StaticAssetServers
    {

        /**
         * Select this framework as default static asset server.
         */
        case SHANI;

        /**
         * Select Apache as default static asset server.
         * You must configure Apache to serve static assets.
         * For public assets use /0/ as an alias.
         */
        case APACHE;

        /**
         * Select Nginx as default static asset server.
         * You must configure Nginx to serve static assets.
         * For public assets use /0/ as an alias.
         */
        case NGINX;

        /**
         * Disable serving static assets.
         */
        case DISABLE;
    }

}
