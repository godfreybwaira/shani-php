<?php

/**
 * Description of BrowsingPrivacy
 * @author coder
 *
 * Created on: Mar 25, 2025 at 9:46:10 AM
 */

namespace shani\advisors\web {

    enum BrowsingPrivacy: string
    {

        /**
         * Never send the Referrer header (Protect user's privacy)
         */
        case STRICT = 'no-referrer';

        /**
         * Send the Referrer header (See what user is browsing but only on this domain)
         */
        case THIS_DOMAIN = 'same-origin';

        /**
         * Send the Referrer header (i.e see what user is browsing on all domains
         * but do not show the actual content they browse)
         */
        case PARTIALLY = 'strict-origin';

        /**
         * Send the full Referrer header on same-origin requests and only the
         * URL without the path on cross-origin requests
         */
        case NONE = 'strict-origin-when-cross-origin';
    }

}
