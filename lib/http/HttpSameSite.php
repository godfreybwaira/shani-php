<?php

/**
 * SameSite controls whether a cookie is sent on cross‑site requests.
 * @author goddy
 *
 * Created on: Apr 3, 2026 at 6:13:26 PM
 */

namespace lib\http {

    enum HttpSameSite: string
    {

        /**
         * Sent in all contexts, including third‑party; must include Secure and be served over HTTPS.
         * Required for cross‑site embeds like federated login, analytics.
         */
        case NONE = 'None';

        /**
         * Sent on same‑site requests and on top‑level GET navigations (e.g., link clicks),
         * but not on most cross‑site subrequests e.g: via AJAX.
         * Good balance for login cookies that should work when users click links.
         */
        case LAX = 'Lax';

        /**
         * Cookie sent only for requests from the same site. Not sent with cross-site requests, even when clicking a link.
         * Best for highly sensitive cookies (e.g., authentication tokens).
         * Can break usability—users may be logged out when navigating via external links.
         */
        case STRICT = 'Strict';
    }

}
