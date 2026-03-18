<?php

/**
 * Description of Oauth2GrantType
 * @author goddy
 *
 * Created on: Mar 9, 2026 at 3:09:44 PM
 */

namespace lib\oauth2 {

    /**
     * Represents the supported OAuth2 grant types as string values.
     * These constants define the authorization flows that a client
     * application can use when interacting with an OAuth2 server.
     */
    enum Oauth2GrantType: string
    {

        /**
         * Used in the standard authorization code flow, where a client exchanges
         * an authorization code for an access token.
         */
        case AUTHORIZATION_CODE = 'authorization_code';

        /**
         * Used in Mobile & SPA apps; prevents code interception. Recommended over plain <code>AUTHORIZATION_CODE</code>
         */
        case AUTHORIZATION_CODE_PKCE = 'authorization_code_pkce';

        /**
         * Used to obtain a new access token by presenting a valid refresh token,
         * typically after the original token expires. Flow requires secure storage
         * of refresh tokens; mishandling can lead to long-term compromise of user accounts.
         */
        case REFRESH_TOKEN = 'refresh_token';

        /**
         * Used when the client itself (not a user) needs to authenticate, often
         * for server-to-server communication. Flow should only be used for trusted clients
         * (e.g., backend services), not public-facing apps
         */
        case CLIENT_CREDENTIALS = 'client_credentials';

        /**
         *  Used when the client directly exchanges a username and password for
         * an access token. This flow is generally discouraged due to security
         * risks, but may be used in legacy systems.
         */
        case PASSWORD = 'password';

        /**
         * Useful for IoT or TV apps where typing credentials directly is impractical.
         */
        case DEVICE_CODE = 'device_code';
    }

}
